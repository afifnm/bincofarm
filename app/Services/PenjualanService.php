<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\PenjualanMelon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenjualanService
{
    public function __construct(private readonly KasService $kasService) {}

    public function simpan(array $data): PenjualanMelon
    {
        return DB::transaction(function () use ($data): PenjualanMelon {
            $items = $this->hitungItems($data['items']);
            $total = $this->totalDari($items);

            $penjualan = PenjualanMelon::create([
                'no_nota'       => $this->generateNoNota(Carbon::parse($data['tanggal'])),
                'greenhouse_id' => $data['greenhouse_id'],
                'nama_pembeli'  => $data['nama_pembeli'],
                'total'         => $total,
                'tanggal'       => $data['tanggal'],
                'user_id'       => $data['user_id'],
            ]);

            $penjualan->items()->createMany($items);

            $greenhouse = Greenhouse::findOrFail($data['greenhouse_id']);

            $this->kasService->createTransaksi([
                'kas_id'      => $greenhouse->kas_id,
                'tanggal'     => $data['tanggal'],
                'tipe'        => 'masuk',
                'jumlah'      => $total,
                'keterangan'  => "Penjualan melon {$penjualan->no_nota} GH {$greenhouse->nama} - {$data['nama_pembeli']}",
                'sumber_type' => PenjualanMelon::class,
                'sumber_id'   => $penjualan->id,
                'user_id'     => $data['user_id'],
            ]);

            return $penjualan;
        });
    }

    public function update(PenjualanMelon $penjualan, array $data): PenjualanMelon
    {
        return DB::transaction(function () use ($penjualan, $data): PenjualanMelon {
            $items = $this->hitungItems($data['items']);
            $total = $this->totalDari($items);

            // Void transaksi kas lama jika ada dan belum di-void
            $trxLama = $penjualan->transaksiKas()->first();
            if ($trxLama && ! $trxLama->is_void) {
                $this->kasService->voidTransaksi($trxLama);
            }

            $penjualan->update([
                'nama_pembeli' => $data['nama_pembeli'],
                'total'        => $total,
                'tanggal'      => $data['tanggal'],
            ]);

            $penjualan->items()->delete();
            $penjualan->items()->createMany($items);

            $greenhouse = $penjualan->greenhouse;

            $this->kasService->createTransaksi([
                'kas_id'      => $greenhouse->kas_id,
                'tanggal'     => $data['tanggal'],
                'tipe'        => 'masuk',
                'jumlah'      => $total,
                'keterangan'  => "Penjualan melon {$penjualan->no_nota} GH {$greenhouse->nama} - {$data['nama_pembeli']}",
                'sumber_type' => PenjualanMelon::class,
                'sumber_id'   => $penjualan->id,
                'user_id'     => $data['user_id'],
            ]);

            return $penjualan->fresh(['items']);
        });
    }

    public function hapus(PenjualanMelon $penjualan): void
    {
        DB::transaction(function () use ($penjualan): void {
            $trx = $penjualan->transaksiKas()->where('is_void', false)->first();
            if ($trx) {
                $this->kasService->voidTransaksi($trx);
            }
            $penjualan->delete();
        });
    }

    public function generateNoNota(Carbon $tanggal): string
    {
        $prefix = 'NJL-' . $tanggal->format('Ymd') . '-';
        $last   = PenjualanMelon::withTrashed()
            ->where('no_nota', 'like', $prefix . '%')
            ->orderByDesc('no_nota')
            ->value('no_nota');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung subtotal tiap item dengan presisi desimal.
     *
     * @param array<array{jenis_melon_id: int|string, jumlah_kg: float|string, harga_per_kg: float|string}> $items
     * @return array<array{jenis_melon_id: int, jumlah_kg: string, harga_per_kg: string, subtotal: string}>
     */
    private function hitungItems(array $items): array
    {
        return array_map(fn (array $item): array => [
            'jenis_melon_id' => (int) $item['jenis_melon_id'],
            'jumlah_kg'      => (string) $item['jumlah_kg'],
            'harga_per_kg'   => (string) $item['harga_per_kg'],
            'subtotal'       => bcmul((string) $item['jumlah_kg'], (string) $item['harga_per_kg'], 2),
        ], $items);
    }

    private function totalDari(array $items): string
    {
        return array_reduce($items, fn (string $carry, array $item): string => bcadd($carry, $item['subtotal'], 2), '0');
    }
}
