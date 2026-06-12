<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarangResource;
use App\Http\Resources\KasResource;
use App\Models\Barang;
use App\Models\Kas;
use App\Models\PanenMelon;
use App\Models\PenjualanMelon;
use App\Models\PopulasiPohonHistori;
use App\Models\TransaksiKas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // PJ GH tidak boleh melihat saldo kas & stok — tampilkan ringkasan GH-nya saja
        if ($request->user()->isPjGh()) {
            return $this->dashboardPjGh($request);
        }

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

    private function dashboardPjGh(Request $request): JsonResponse
    {
        $bulanIni   = Carbon::now()->startOfMonth()->toDateString();
        $akhirBulan = Carbon::now()->endOfMonth()->toDateString();

        $greenhouses = $request->user()->greenhouses()
            ->with('populasi')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $ghIds = $greenhouses->pluck('id');

        $panenBulanIni = (float) PanenMelon::whereIn('greenhouse_id', $ghIds)
            ->whereBetween('tanggal', [$bulanIni, $akhirBulan])
            ->sum('berat');

        $jualBulanIni = (float) \App\Models\PenjualanMelonItem::whereHas('penjualan', fn ($q) => $q
            ->whereIn('greenhouse_id', $ghIds)
            ->whereBetween('tanggal', [$bulanIni, $akhirBulan]))
            ->sum('jumlah_kg');

        $rekapPanenPerGh = PanenMelon::selectRaw('greenhouse_id, SUM(berat) as total_kg, COUNT(*) as jumlah_panen')
            ->whereIn('greenhouse_id', $ghIds)
            ->whereBetween('tanggal', [$bulanIni, $akhirBulan])
            ->groupBy('greenhouse_id')
            ->get()
            ->keyBy('greenhouse_id');

        $historiTerbaru = PopulasiPohonHistori::whereIn('greenhouse_id', $ghIds)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($h) => [
                'greenhouse_id'   => $h->greenhouse_id,
                'greenhouse_nama' => $greenhouses->firstWhere('id', $h->greenhouse_id)?->nama,
                'total_pohon_baru'=> $h->total_pohon_baru,
                'pohon_hidup_baru'=> $h->pohon_hidup_baru,
                'pohon_mati_baru' => $h->pohon_mati_baru,
                'catatan'         => $h->catatan,
                'user_nama'       => $h->user?->name,
                'created_at'      => $h->created_at,
            ]);

        return response()->json([
            'total_saldo'  => null,
            'kas'          => [],
            'stok_menipis' => [],
            'greenhouse'   => [
                'daftar' => $greenhouses->map(fn ($gh) => [
                    'id'              => $gh->id,
                    'nama'            => $gh->nama,
                    'lokasi'          => $gh->lokasi,
                    'pohon_hidup'     => $gh->populasi?->pohon_hidup ?? 0,
                    'pohon_mati'      => $gh->populasi?->pohon_mati ?? 0,
                    'total_pohon'     => $gh->populasi?->total_pohon ?? 0,
                    'panen_bulan_ini' => (float) ($rekapPanenPerGh[$gh->id]?->total_kg ?? 0),
                    'jumlah_panen'    => (int) ($rekapPanenPerGh[$gh->id]?->jumlah_panen ?? 0),
                ])->values(),
                'panen_bulan_ini_kg' => $panenBulanIni,
                'jual_bulan_ini_kg'  => $jualBulanIni,
                'histori_populasi'   => $historiTerbaru,
            ],
            'periode_info' => [
                'bulan' => Carbon::now()->format('Y-m'),
            ],
        ]);
    }
}
