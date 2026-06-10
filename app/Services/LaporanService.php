<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Barang;
use App\Models\Kas;
use App\Models\MutasiBarang;
use App\Models\SaldoPeriode;
use App\Models\StokPeriode;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanService
{
    public function saldoAwalKas(int $kasId, Carbon $tanggal): string
    {
        $bulanMulai = Carbon::create($tanggal->year, $tanggal->month, 1);

        $snapshot = SaldoPeriode::where('kas_id', $kasId)
            ->where('periode', '<', $bulanMulai->toDateString())
            ->orderByDesc('periode')
            ->first();

        if ($snapshot) {
            $basis   = $snapshot->saldo_akhir;
            $dariTgl = Carbon::parse($snapshot->periode)->addMonth()->startOfMonth();
        } else {
            $kas   = Kas::findOrFail($kasId);
            $basis = $kas->saldo_awal;
            $dariTgl = Carbon::createFromTimestamp(0);
        }

        $delta = TransaksiKas::where('kas_id', $kasId)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$dariTgl->toDateString(), $tanggal->copy()->subDay()->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN tipe IN ('masuk','transfer_masuk') THEN jumlah ELSE 0 END) as total_masuk,
                SUM(CASE WHEN tipe IN ('keluar','transfer_keluar') THEN jumlah ELSE 0 END) as total_keluar
            ")
            ->first();

        $masuk  = $delta ? (float) $delta->total_masuk : 0;
        $keluar = $delta ? (float) $delta->total_keluar : 0;

        return bcadd(bcsub(bcadd((string) $basis, (string) $masuk, 2), (string) $keluar, 2), '0', 2);
    }

    public function cashflow(int|null $kasId, Carbon $dari, Carbon $sampai): array
    {
        if ($kasId) {
            return [$this->cashflowSingleKas($kasId, $dari, $sampai)];
        }

        return Kas::where('is_active', true)->get()->map(function (Kas $kas) use ($dari, $sampai): array {
            return $this->cashflowSingleKas($kas->id, $dari, $sampai);
        })->values()->toArray();
    }

    public function cashflowSingleKas(int $kasId, Carbon $dari, Carbon $sampai): array
    {
        $kas       = Kas::findOrFail($kasId);
        $saldoAwal = $this->saldoAwalKas($kasId, $dari);

        $transaksi = TransaksiKas::with('kategori')
            ->where('kas_id', $kasId)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $saldoBerjalan = (float) $saldoAwal;
        $totalMasuk    = '0';
        $totalKeluar   = '0';

        $rows = $transaksi->map(function (TransaksiKas $trx) use (&$saldoBerjalan, &$totalMasuk, &$totalKeluar): array {
            $masuk  = $trx->tipe->isDebit() ? (float) $trx->jumlah : 0;
            $keluar = !$trx->tipe->isDebit() ? (float) $trx->jumlah : 0;

            $saldoBerjalan = (float) bcadd(bcsub(
                (string) $saldoBerjalan, (string) $keluar, 2
            ) , (string) $masuk, 2);

            $totalMasuk  = bcadd($totalMasuk, (string) $masuk, 2);
            $totalKeluar = bcadd($totalKeluar, (string) $keluar, 2);

            return [
                'id'              => $trx->id,
                'tanggal'         => $trx->tanggal->toDateString(),
                'nomor'           => $trx->nomor,
                'kategori'        => $trx->kategori?->nama,
                'tipe'            => $trx->tipe->value,
                'keterangan'      => $trx->keterangan,
                'masuk'           => $masuk,
                'keluar'          => $keluar,
                'saldo_berjalan'  => $saldoBerjalan,
            ];
        });

        return [
            'kas'          => ['id' => $kas->id, 'nama' => $kas->nama, 'kode' => $kas->kode],
            'saldo_awal'   => (float) $saldoAwal,
            'transaksi'    => $rows->values()->toArray(),
            'total_masuk'  => (float) $totalMasuk,
            'total_keluar' => (float) $totalKeluar,
            'saldo_akhir'  => $saldoBerjalan,
        ];
    }

    public function stokAwalBarang(int $barangId, Carbon $tanggal): string
    {
        $bulanMulai = Carbon::create($tanggal->year, $tanggal->month, 1);

        $snapshot = StokPeriode::where('barang_id', $barangId)
            ->where('periode', '<', $bulanMulai->toDateString())
            ->orderByDesc('periode')
            ->first();

        if ($snapshot) {
            $basis   = $snapshot->stok_akhir;
            $dariTgl = Carbon::parse($snapshot->periode)->addMonth()->startOfMonth();
        } else {
            $barang  = Barang::findOrFail($barangId);
            $basis   = '0';
            $dariTgl = Carbon::createFromTimestamp(0);
        }

        $delta = MutasiBarang::where('barang_id', $barangId)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$dariTgl->toDateString(), $tanggal->copy()->subDay()->toDateString()])
            ->selectRaw("
                SUM(CASE WHEN tipe = 'masuk' THEN qty WHEN tipe = 'penyesuaian' THEN qty ELSE 0 END) as total_masuk,
                SUM(CASE WHEN tipe = 'keluar' THEN qty ELSE 0 END) as total_keluar
            ")
            ->first();

        $masuk  = $delta ? (float) $delta->total_masuk : 0;
        $keluar = $delta ? (float) $delta->total_keluar : 0;

        return bcadd(bcsub(bcadd((string) $basis, (string) $masuk, 2), (string) $keluar, 2), '0', 2);
    }

    public function kartuStok(int $barangId, Carbon $dari, Carbon $sampai): array
    {
        $barang   = Barang::findOrFail($barangId);
        $stokAwal = $this->stokAwalBarang($barangId, $dari);

        $mutasi = MutasiBarang::where('barang_id', $barangId)
            ->where('is_void', false)
            ->whereBetween('tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $stokBerjalan = (float) $stokAwal;
        $totalMasuk   = '0';
        $totalKeluar  = '0';

        $rows = $mutasi->map(function (MutasiBarang $m) use (&$stokBerjalan, &$totalMasuk, &$totalKeluar): array {
            $masuk  = in_array($m->tipe->value, ['masuk', 'penyesuaian']) ? (float) $m->qty : 0;
            $keluar = $m->tipe->value === 'keluar' ? (float) $m->qty : 0;

            $stokBerjalan = (float) bcadd(bcsub(
                (string) $stokBerjalan, (string) $keluar, 2
            ), (string) $masuk, 2);

            $totalMasuk  = bcadd($totalMasuk, (string) $masuk, 2);
            $totalKeluar = bcadd($totalKeluar, (string) $keluar, 2);

            return [
                'id'             => $m->id,
                'tanggal'        => $m->tanggal->toDateString(),
                'nomor'          => $m->nomor,
                'tipe'           => $m->tipe->value,
                'keterangan'     => $m->keterangan,
                'referensi'      => $m->referensi,
                'masuk'          => $masuk,
                'keluar'         => $keluar,
                'stok_berjalan'  => $stokBerjalan,
                'harga_satuan'   => (float) $m->harga_satuan,
            ];
        });

        return [
            'barang'       => ['id' => $barang->id, 'nama' => $barang->nama, 'kode' => $barang->kode, 'satuan' => $barang->satuan],
            'stok_awal'    => (float) $stokAwal,
            'mutasi'       => $rows->values()->toArray(),
            'total_masuk'  => (float) $totalMasuk,
            'total_keluar' => (float) $totalKeluar,
            'stok_akhir'   => $stokBerjalan,
        ];
    }
}
