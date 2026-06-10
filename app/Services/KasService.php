<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipeTransaksi;
use App\Jobs\RecalcSnapshotPeriode;
use App\Models\Kas;
use App\Models\SaldoPeriode;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class KasService
{
    public function createTransaksi(array $data): TransaksiKas
    {
        return DB::transaction(function () use ($data): TransaksiKas {
            $tanggal = Carbon::parse($data['tanggal']);
            $this->assertPeriodeTerbuka($data['kas_id'], $tanggal);

            $kas = Kas::lockForUpdate()->findOrFail($data['kas_id']);

            $tipe   = TipeTransaksi::from($data['tipe']);
            $jumlah = (float) $data['jumlah'];

            $nomor = $this->generateNomor($tanggal);

            $trx = TransaksiKas::create([
                'nomor'       => $nomor,
                'kas_id'      => $kas->id,
                'kategori_id' => $data['kategori_id'] ?? null,
                'tanggal'     => $tanggal->toDateString(),
                'tipe'        => $tipe,
                'jumlah'      => $jumlah,
                'keterangan'  => $data['keterangan'] ?? null,
                'sumber_type' => $data['sumber_type'] ?? null,
                'sumber_id'   => $data['sumber_id'] ?? null,
                'user_id'     => $data['user_id'] ?? null,
            ]);

            if ($tipe->isDebit()) {
                $kas->saldo_berjalan = bcadd((string) $kas->saldo_berjalan, (string) $jumlah, 2);
            } else {
                $kas->saldo_berjalan = bcsub((string) $kas->saldo_berjalan, (string) $jumlah, 2);
            }
            $kas->save();

            $this->dispatchRecalcIfNeeded($kas->id, $tanggal);

            return $trx;
        });
    }

    public function voidTransaksi(TransaksiKas $trx): TransaksiKas
    {
        return DB::transaction(function () use ($trx): TransaksiKas {
            if ($trx->is_void) {
                throw new HttpException(409, 'Transaksi sudah di-void.');
            }
            $tanggal = Carbon::parse($trx->tanggal);
            $this->assertPeriodeTerbuka($trx->kas_id, $tanggal);

            $kas = Kas::lockForUpdate()->findOrFail($trx->kas_id);

            if ($trx->tipe->isDebit()) {
                $kas->saldo_berjalan = bcsub((string) $kas->saldo_berjalan, (string) $trx->jumlah, 2);
            } else {
                $kas->saldo_berjalan = bcadd((string) $kas->saldo_berjalan, (string) $trx->jumlah, 2);
            }
            $kas->save();

            $trx->update(['is_void' => true, 'void_at' => now()]);

            $this->dispatchRecalcIfNeeded($kas->id, $tanggal);

            return $trx;
        });
    }

    public function generateNomor(Carbon $tanggal): string
    {
        $prefix = 'TRX-' . $tanggal->format('Ymd') . '-';
        $last   = TransaksiKas::where('nomor', 'like', $prefix . '%')
            ->orderByDesc('nomor')
            ->value('nomor');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function assertPeriodeTerbuka(int $kasId, Carbon $tanggal): void
    {
        $periode = Carbon::create($tanggal->year, $tanggal->month, 1);
        $closed  = SaldoPeriode::where('kas_id', $kasId)
            ->where('periode', $periode->toDateString())
            ->where('is_closed', true)
            ->exists();

        if ($closed) {
            throw new HttpException(409, "Periode {$periode->format('Y-m')} sudah ditutup.");
        }
    }

    private function dispatchRecalcIfNeeded(int $kasId, Carbon $tanggal): void
    {
        $periode = Carbon::create($tanggal->year, $tanggal->month, 1)->toDateString();
        $hasSnapshot = SaldoPeriode::where('kas_id', $kasId)
            ->where('periode', $periode)
            ->where('is_closed', false)
            ->exists();

        if ($hasSnapshot) {
            RecalcSnapshotPeriode::dispatch('kas', $kasId, $periode);
        }
    }
}
