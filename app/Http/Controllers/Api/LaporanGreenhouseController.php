<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Greenhouse;
use App\Models\PanenMelon;
use App\Models\PenjualanMelon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanGreenhouseController extends Controller
{
    public function rekapPanen(Request $request): JsonResponse
    {
        $query = PanenMelon::query()
            ->select([
                'greenhouse_id',
                'jenis_melon_id',
                'grade',
                DB::raw('SUM(berat) as total_berat'),
                DB::raw('COUNT(*) as jumlah_panen'),
            ])
            ->with(['greenhouse:id,nama', 'jenisMelon:id,nama'])
            ->groupBy('greenhouse_id', 'jenis_melon_id', 'grade')
            ->orderBy('greenhouse_id')
            ->orderBy('jenis_melon_id')
            ->orderBy('grade');

        // PJ GH hanya melihat laporan GH yang ditugaskan padanya
        if ($request->user()->isPjGh()) {
            $query->whereRelation('greenhouse', 'user_id', $request->user()->id);
        }

        if ($request->filled('greenhouse_id')) {
            $query->where('greenhouse_id', $request->integer('greenhouse_id'));
        }
        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->input('sampai'));
        }

        $rows = $query->get()->map(fn ($r) => [
            'greenhouse_id'  => $r->greenhouse_id,
            'greenhouse'     => $r->greenhouse?->nama,
            'jenis_melon_id' => $r->jenis_melon_id,
            'jenis_melon'    => $r->jenisMelon?->nama,
            'grade'          => $r->grade,
            'total_berat'    => (float) $r->total_berat,
            'jumlah_panen'   => (int) $r->jumlah_panen,
        ]);

        return response()->json(['data' => $rows]);
    }

    public function perbandingan(Request $request): JsonResponse
    {
        $ghQuery = Greenhouse::query()->with('populasi');

        // PJ GH hanya melihat perbandingan GH yang ditugaskan padanya
        if ($request->user()->isPjGh()) {
            $ghQuery->where('user_id', $request->user()->id);
        }

        if ($request->filled('greenhouse_id')) {
            $ghQuery->where('id', $request->integer('greenhouse_id'));
        }
        $greenhouses = $ghQuery->orderBy('nama')->get();

        $panenAgg = PanenMelon::query()
            ->select('greenhouse_id', DB::raw('SUM(berat) as total_panen_kg'))
            ->when($request->filled('dari'), fn ($q) => $q->where('tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('greenhouse_id'), fn ($q) => $q->where('greenhouse_id', $request->integer('greenhouse_id')))
            ->groupBy('greenhouse_id')
            ->pluck('total_panen_kg', 'greenhouse_id');

        $jualAgg = PenjualanMelon::query()
            ->join('penjualan_melon_item', 'penjualan_melon_item.penjualan_melon_id', '=', 'penjualan_melon.id')
            ->select('penjualan_melon.greenhouse_id', DB::raw('SUM(penjualan_melon_item.jumlah_kg) as total_jual_kg'), DB::raw('SUM(penjualan_melon_item.subtotal) as total_nilai'))
            ->when($request->filled('dari'), fn ($q) => $q->where('penjualan_melon.tanggal', '>=', $request->input('dari')))
            ->when($request->filled('sampai'), fn ($q) => $q->where('penjualan_melon.tanggal', '<=', $request->input('sampai')))
            ->when($request->filled('greenhouse_id'), fn ($q) => $q->where('penjualan_melon.greenhouse_id', $request->integer('greenhouse_id')))
            ->groupBy('penjualan_melon.greenhouse_id')
            ->get()
            ->keyBy('greenhouse_id');

        $result = $greenhouses->map(function ($gh) use ($panenAgg, $jualAgg) {
            $totalPanen = (float) ($panenAgg[$gh->id] ?? 0);
            $jual       = $jualAgg[$gh->id] ?? null;
            $totalJual  = $jual ? (float) $jual->total_jual_kg : 0;
            $totalNilai = $jual ? (float) $jual->total_nilai : 0;

            return [
                'greenhouse_id'  => $gh->id,
                'greenhouse'     => $gh->nama,
                'total_panen_kg' => $totalPanen,
                'total_jual_kg'  => $totalJual,
                'sisa_kg'        => max(0, $totalPanen - $totalJual),
                'total_nilai_rp' => $totalNilai,
                'pohon_hidup'    => $gh->populasi?->pohon_hidup ?? 0,
                'pohon_mati'     => $gh->populasi?->pohon_mati ?? 0,
            ];
        });

        return response()->json(['data' => $result]);
    }
}
