<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct(private readonly LaporanService $laporanService) {}

    public function cashflow(Request $request): JsonResponse
    {
        $request->validate([
            'dari'   => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
            'kas_id' => ['nullable', 'exists:kas,id'],
        ]);

        $dari   = Carbon::parse($request->input('dari'));
        $sampai = Carbon::parse($request->input('sampai'));
        $kasId  = $request->filled('kas_id') ? (int) $request->input('kas_id') : null;

        $data = $this->laporanService->cashflow($kasId, $dari, $sampai);

        return response()->json($data);
    }

    public function kartuStok(Request $request): JsonResponse
    {
        $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'dari'      => ['required', 'date'],
            'sampai'    => ['required', 'date', 'after_or_equal:dari'],
        ]);

        $dari      = Carbon::parse($request->input('dari'));
        $sampai    = Carbon::parse($request->input('sampai'));
        $barangId  = (int) $request->input('barang_id');

        $data = $this->laporanService->kartuStok($barangId, $dari, $sampai);

        return response()->json($data);
    }
}
