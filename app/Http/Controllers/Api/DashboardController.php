<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarangResource;
use App\Http\Resources\KasResource;
use App\Models\Barang;
use App\Models\Kas;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $kasAktif = Kas::where('is_active', true)->get();

        $bulanIni  = Carbon::now()->startOfMonth();
        $akhirBulan = Carbon::now()->endOfMonth();

        $ringkasan = $kasAktif->map(function (Kas $kas) use ($bulanIni, $akhirBulan): array {
            $agg = TransaksiKas::where('kas_id', $kas->id)
                ->where('is_void', false)
                ->whereBetween('tanggal', [$bulanIni->toDateString(), $akhirBulan->toDateString()])
                ->selectRaw("
                    SUM(CASE WHEN tipe IN ('masuk','transfer_masuk') THEN jumlah ELSE 0 END) as total_masuk,
                    SUM(CASE WHEN tipe IN ('keluar','transfer_keluar') THEN jumlah ELSE 0 END) as total_keluar
                ")
                ->first();

            return [
                'kas'         => new KasResource($kas),
                'total_masuk' => (float) ($agg?->total_masuk ?? 0),
                'total_keluar'=> (float) ($agg?->total_keluar ?? 0),
            ];
        });

        $stokMenipis = Barang::where('is_active', true)
            ->whereRaw('stok <= stok_minimum')
            ->orderBy('nama')
            ->get();

        $totalSaldo = $kasAktif->sum(fn($k) => (float) $k->saldo_berjalan);

        return response()->json([
            'total_saldo'  => $totalSaldo,
            'kas'          => $ringkasan->values(),
            'stok_menipis' => BarangResource::collection($stokMenipis),
            'periode_info' => [
                'bulan' => Carbon::now()->format('Y-m'),
            ],
        ]);
    }
}
