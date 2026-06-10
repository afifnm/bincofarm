<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipeMutasi;
use App\Enums\TipeTransaksi;
use App\Jobs\RecalcSnapshotPeriode;
use App\Models\Barang;
use App\Models\MutasiBarang;
use App\Models\StokPeriode;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BarangService
{
    public function __construct(private readonly KasService $kasService) {}

    public function createMutasi(array $data): MutasiBarang
    {
        return DB::transaction(function () use ($data): MutasiBarang {
            $tanggal = Carbon::parse($data['tanggal']);
            $this->assertPeriodeTerbuka($data['barang_id'], $tanggal);

            $barang = Barang::lockForUpdate()->findOrFail($data['barang_id']);
            $tipe   = TipeMutasi::from($data['tipe']);
            $qty    = (float) $data['qty'];

            if ($tipe === TipeMutasi::Keluar && $barang->stok < $qty) {
                throw new HttpException(409, "Stok tidak cukup. Stok tersedia: {$barang->stok} {$barang->satuan}.");
            }

            $nomor = $this->generateNomor($tipe, $tanggal);

            $stokSetelah = match ($tipe) {
                TipeMutasi::Masuk  => bcadd((string) $barang->stok, (string) $qty, 2),
                TipeMutasi::Keluar => bcsub((string) $barang->stok, (string) $qty, 2),
                TipeMutasi::Penyesuaian => (string) $qty,
            };

            $mutasi = MutasiBarang::create([
                'nomor'        => $nomor,
                'barang_id'    => $barang->id,
                'tanggal'      => $tanggal->toDateString(),
                'tipe'         => $tipe,
                'qty'          => $qty,
                'harga_satuan' => $data['harga_satuan'] ?? 0,
                'stok_setelah' => $stokSetelah,
                'referensi'    => $data['referensi'] ?? null,
                'keterangan'   => $data['keterangan'] ?? null,
                'user_id'      => $data['user_id'] ?? null,
            ]);

            $barang->stok = $stokSetelah;
            if ($tipe === TipeMutasi::Masuk && isset($data['harga_satuan'])) {
                $barang->harga_beli = $data['harga_satuan'];
            }
            $barang->save();

            if (!empty($data['kas_id'])) {
                $this->createLinkedTransaksiKas($mutasi, $data, $tanggal);
            }

            $this->dispatchRecalcIfNeeded($barang->id, $tanggal);

            return $mutasi;
        });
    }

    public function voidMutasi(MutasiBarang $mutasi): MutasiBarang
    {
        return DB::transaction(function () use ($mutasi): MutasiBarang {
            if ($mutasi->is_void) {
                throw new HttpException(409, 'Mutasi sudah di-void.');
            }

            $tanggal = Carbon::parse($mutasi->tanggal);
            $this->assertPeriodeTerbuka($mutasi->barang_id, $tanggal);

            $barang = Barang::lockForUpdate()->findOrFail($mutasi->barang_id);

            $stokSetelah = match ($mutasi->tipe) {
                TipeMutasi::Masuk  => bcsub((string) $barang->stok, (string) $mutasi->qty, 2),
                TipeMutasi::Keluar => bcadd((string) $barang->stok, (string) $mutasi->qty, 2),
                TipeMutasi::Penyesuaian => (string) $barang->stok,
            };

            $barang->stok = $stokSetelah;
            $barang->save();

            $mutasi->update(['is_void' => true, 'void_at' => now()]);

            // void linked kas transactions
            $mutasi->transaksiKas()->where('is_void', false)->each(function (TransaksiKas $trx): void {
                $this->kasService->voidTransaksi($trx);
            });

            $this->dispatchRecalcIfNeeded($barang->id, $tanggal);

            return $mutasi;
        });
    }

    public function generateNomor(TipeMutasi $tipe, Carbon $tanggal): string
    {
        $prefix = $tipe->nomorPrefix() . '-' . $tanggal->format('Ymd') . '-';
        $last   = MutasiBarang::where('nomor', 'like', $prefix . '%')
            ->orderByDesc('nomor')
            ->value('nomor');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function assertPeriodeTerbuka(int $barangId, Carbon $tanggal): void
    {
        $periode = Carbon::create($tanggal->year, $tanggal->month, 1);
        $closed  = StokPeriode::where('barang_id', $barangId)
            ->where('periode', $periode->toDateString())
            ->where('is_closed', true)
            ->exists();

        if ($closed) {
            throw new HttpException(409, "Periode {$periode->format('Y-m')} untuk barang ini sudah ditutup.");
        }
    }

    private function createLinkedTransaksiKas(MutasiBarang $mutasi, array $data, Carbon $tanggal): void
    {
        $tipeKas = $mutasi->tipe === TipeMutasi::Masuk
            ? TipeTransaksi::Keluar
            : TipeTransaksi::Masuk;

        $this->kasService->createTransaksi([
            'kas_id'       => $data['kas_id'],
            'kategori_id'  => $data['kategori_id'] ?? null,
            'tanggal'      => $tanggal->toDateString(),
            'tipe'         => $tipeKas->value,
            'jumlah'       => bcmul((string) $mutasi->qty, (string) $mutasi->harga_satuan, 2),
            'keterangan'   => $data['keterangan'] ?? $mutasi->nomor,
            'sumber_type'  => MutasiBarang::class,
            'sumber_id'    => $mutasi->id,
            'user_id'      => $data['user_id'] ?? null,
        ]);
    }

    private function dispatchRecalcIfNeeded(int $barangId, Carbon $tanggal): void
    {
        $periode = Carbon::create($tanggal->year, $tanggal->month, 1)->toDateString();
        $hasSnapshot = StokPeriode::where('barang_id', $barangId)
            ->where('periode', $periode)
            ->where('is_closed', false)
            ->exists();

        if ($hasSnapshot) {
            RecalcSnapshotPeriode::dispatch('barang', $barangId, $periode);
        }
    }
}
