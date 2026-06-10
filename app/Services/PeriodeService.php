<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Barang;
use App\Models\Kas;
use App\Models\MutasiBarang;
use App\Models\SaldoPeriode;
use App\Models\StokPeriode;
use App\Models\TransaksiKas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PeriodeService
{
    public function tutupPeriode(string $periodeStr, User $user): void
    {
        DB::transaction(function () use ($periodeStr, $user): void {
            $periode = Carbon::parse($periodeStr)->startOfMonth();
            $this->assertBerurutan($periode);

            $akhirBulan = $periode->copy()->endOfMonth();

            foreach (Kas::where('is_active', true)->get() as $kas) {
                $this->finalizeSaldoPeriode($kas, $periode, $akhirBulan, $user);
            }

            foreach (Barang::where('is_active', true)->get() as $barang) {
                $this->finalizeStokPeriode($barang, $periode, $akhirBulan);
            }
        });
    }

    public function bukaPeriode(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $last = SaldoPeriode::where('is_closed', true)
                ->orderByDesc('periode')
                ->first();

            if (!$last) {
                throw new HttpException(409, 'Tidak ada periode yang tertutup.');
            }

            SaldoPeriode::where('periode', $last->periode->toDateString())
                ->update(['is_closed' => false, 'closed_at' => null, 'closed_by' => null]);

            StokPeriode::where('periode', $last->periode->toDateString())
                ->update(['is_closed' => false]);
        });
    }

    public function generateDraftSnapshot(Carbon $periode): void
    {
        $akhirBulan = $periode->copy()->endOfMonth();

        foreach (Kas::where('is_active', true)->get() as $kas) {
            $existing = SaldoPeriode::where('kas_id', $kas->id)
                ->where('periode', $periode->toDateString())
                ->first();

            if (!$existing || !$existing->is_closed) {
                $this->finalizeSaldoPeriode($kas, $periode, $akhirBulan, null, false);
            }
        }

        foreach (Barang::where('is_active', true)->get() as $barang) {
            $existing = StokPeriode::where('barang_id', $barang->id)
                ->where('periode', $periode->toDateString())
                ->first();

            if (!$existing || !$existing->is_closed) {
                $this->finalizeStokPeriode($barang, $periode, $akhirBulan, false);
            }
        }
    }

    public function recalcKasSince(int $kasId, string $fromPeriode): void
    {
        $periode = Carbon::parse($fromPeriode)->startOfMonth();
        $now     = now()->startOfMonth();

        while ($periode->lte($now)) {
            $existing = SaldoPeriode::where('kas_id', $kasId)
                ->where('periode', $periode->toDateString())
                ->first();

            if ($existing) {
                $this->recalcSaldoPeriode($kasId, $periode);
            }
            $periode->addMonth();
        }
    }

    public function recalcBarangSince(int $barangId, string $fromPeriode): void
    {
        $periode = Carbon::parse($fromPeriode)->startOfMonth();
        $now     = now()->startOfMonth();

        while ($periode->lte($now)) {
            $existing = StokPeriode::where('barang_id', $barangId)
                ->where('periode', $periode->toDateString())
                ->first();

            if ($existing) {
                $this->recalcStokPeriode($barangId, $periode);
            }
            $periode->addMonth();
        }
    }

    private function assertBerurutan(Carbon $periode): void
    {
        $prevPeriode = $periode->copy()->subMonth();

        $hasTransactions = TransaksiKas::where('tanggal', '<', $periode->toDateString())
            ->exists();

        if ($hasTransactions) {
            $prevClosed = SaldoPeriode::where('periode', $prevPeriode->toDateString())
                ->where('is_closed', true)
                ->count();

            $kasCount = Kas::where('is_active', true)->count();

            if ($kasCount > 0 && $prevClosed < $kasCount) {
                $kasUnclosed = Kas::where('is_active', true)
                    ->whereDoesntHave('saldoPeriode', function ($q) use ($prevPeriode): void {
                        $q->where('periode', $prevPeriode->toDateString())
                          ->where('is_closed', true);
                    })
                    ->exists();

                if ($kasUnclosed) {
                    throw new HttpException(409, "Periode {$prevPeriode->format('Y-m')} belum ditutup. Tutup berurutan.");
                }
            }
        }
    }

    private function finalizeSaldoPeriode(Kas $kas, Carbon $periode, Carbon $akhirBulan, ?User $user, bool $close = true): void
    {
        $snapshot = app(LaporanService::class)->saldoAwalKas($kas->id, $periode);

        $agg = TransaksiKas::where('kas_id', $kas->id)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$periode->toDateString(), $akhirBulan->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN tipe IN ('masuk','transfer_masuk') THEN jumlah ELSE 0 END) as total_masuk,
                SUM(CASE WHEN tipe IN ('keluar','transfer_keluar') THEN jumlah ELSE 0 END) as total_keluar
            ")
            ->first();

        $masuk  = $agg ? (float) $agg->total_masuk  : 0;
        $keluar = $agg ? (float) $agg->total_keluar : 0;
        $akhir  = bcadd(bcsub(bcadd((string) $snapshot, (string) $masuk, 2), (string) $keluar, 2), '0', 2);

        SaldoPeriode::updateOrCreate(
            ['kas_id' => $kas->id, 'periode' => $periode->toDateString()],
            [
                'saldo_akhir'  => $akhir,
                'total_masuk'  => $masuk,
                'total_keluar' => $keluar,
                'is_closed'    => $close,
                'closed_at'    => $close ? now() : null,
                'closed_by'    => $close ? $user?->id : null,
            ]
        );
    }

    private function finalizeStokPeriode(Barang $barang, Carbon $periode, Carbon $akhirBulan, bool $close = true): void
    {
        $agg = MutasiBarang::where('barang_id', $barang->id)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$periode->toDateString(), $akhirBulan->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN tipe IN ('masuk','penyesuaian') THEN qty ELSE 0 END) as total_masuk,
                SUM(CASE WHEN tipe = 'keluar' THEN qty ELSE 0 END) as total_keluar
            ")
            ->first();

        $stokAwal = app(LaporanService::class)->stokAwalBarang($barang->id, $periode);
        $masuk    = $agg ? (float) $agg->total_masuk  : 0;
        $keluar   = $agg ? (float) $agg->total_keluar : 0;
        $akhir    = bcadd(bcsub(bcadd((string) $stokAwal, (string) $masuk, 2), (string) $keluar, 2), '0', 2);

        StokPeriode::updateOrCreate(
            ['barang_id' => $barang->id, 'periode' => $periode->toDateString()],
            [
                'stok_akhir'   => $akhir,
                'total_masuk'  => $masuk,
                'total_keluar' => $keluar,
                'is_closed'    => $close,
            ]
        );
    }

    private function recalcSaldoPeriode(int $kasId, Carbon $periode): void
    {
        $kas = Kas::findOrFail($kasId);
        $this->finalizeSaldoPeriode($kas, $periode, $periode->copy()->endOfMonth(), null, false);
    }

    private function recalcStokPeriode(int $barangId, Carbon $periode): void
    {
        $barang = Barang::findOrFail($barangId);
        $this->finalizeStokPeriode($barang, $periode, $periode->copy()->endOfMonth(), false);
    }
}
