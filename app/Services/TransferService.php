<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipeTransaksi;
use App\Models\Kas;
use App\Models\SaldoPeriode;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TransferService
{
    public function __construct(private readonly KasService $kasService) {}

    public function transfer(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $tanggal  = Carbon::parse($data['tanggal']);
            $kasAsal  = Kas::lockForUpdate()->findOrFail($data['kas_asal_id']);
            $kasTujuan = Kas::lockForUpdate()->findOrFail($data['kas_tujuan_id']);
            $jumlah   = (float) $data['jumlah'];

            if ($kasAsal->id === $kasTujuan->id) {
                throw new HttpException(422, 'Kas asal dan tujuan tidak boleh sama.');
            }

            $this->kasService->assertPeriodeTerbuka($kasAsal->id, $tanggal);
            $this->kasService->assertPeriodeTerbuka($kasTujuan->id, $tanggal);

            $group  = (string) Str::uuid();
            $nomor1 = $this->kasService->generateNomor($tanggal);

            $trxKeluar = TransaksiKas::create([
                'nomor'          => $nomor1,
                'kas_id'         => $kasAsal->id,
                'kategori_id'    => null,
                'tanggal'        => $tanggal->toDateString(),
                'tipe'           => TipeTransaksi::TransferKeluar,
                'jumlah'         => $jumlah,
                'keterangan'     => $data['keterangan'] ?? "Transfer ke {$kasTujuan->nama}",
                'transfer_group' => $group,
                'user_id'        => $data['user_id'] ?? null,
            ]);

            $nomor2 = $this->kasService->generateNomor($tanggal);

            $trxMasuk = TransaksiKas::create([
                'nomor'          => $nomor2,
                'kas_id'         => $kasTujuan->id,
                'kategori_id'    => null,
                'tanggal'        => $tanggal->toDateString(),
                'tipe'           => TipeTransaksi::TransferMasuk,
                'jumlah'         => $jumlah,
                'keterangan'     => $data['keterangan'] ?? "Transfer dari {$kasAsal->nama}",
                'transfer_group' => $group,
                'user_id'        => $data['user_id'] ?? null,
            ]);

            $kasAsal->saldo_berjalan  = bcsub((string) $kasAsal->saldo_berjalan, (string) $jumlah, 2);
            $kasTujuan->saldo_berjalan = bcadd((string) $kasTujuan->saldo_berjalan, (string) $jumlah, 2);
            $kasAsal->save();
            $kasTujuan->save();

            return [$trxKeluar, $trxMasuk];
        });
    }

    public function voidTransfer(string $transferGroup): void
    {
        DB::transaction(function () use ($transferGroup): void {
            $transaksi = TransaksiKas::where('transfer_group', $transferGroup)
                ->lockForUpdate()
                ->get();

            if ($transaksi->isEmpty()) {
                throw new HttpException(404, 'Transfer tidak ditemukan.');
            }

            if ($transaksi->every(fn($t) => $t->is_void)) {
                throw new HttpException(409, 'Transfer sudah di-void.');
            }

            foreach ($transaksi as $trx) {
                $tanggal = Carbon::parse($trx->tanggal);
                $this->kasService->assertPeriodeTerbuka($trx->kas_id, $tanggal);

                $kas = Kas::lockForUpdate()->findOrFail($trx->kas_id);

                if ($trx->tipe === TipeTransaksi::TransferMasuk) {
                    $kas->saldo_berjalan = bcsub((string) $kas->saldo_berjalan, (string) $trx->jumlah, 2);
                } else {
                    $kas->saldo_berjalan = bcadd((string) $kas->saldo_berjalan, (string) $trx->jumlah, 2);
                }
                $kas->save();
                $trx->update(['is_void' => true, 'void_at' => now()]);
            }
        });
    }
}
