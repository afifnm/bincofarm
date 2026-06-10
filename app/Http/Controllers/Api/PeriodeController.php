<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaldoPeriode;
use App\Services\PeriodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function __construct(private readonly PeriodeService $periodeService) {}

    public function index(): JsonResponse
    {
        $periodes = SaldoPeriode::with('kas')
            ->orderByDesc('periode')
            ->get()
            ->groupBy(fn($row) => $row->periode->format('Y-m'))
            ->map(function ($rows, $periode) {
                return [
                    'periode'    => $periode,
                    'is_closed'  => $rows->every(fn($r) => $r->is_closed),
                    'closed_at'  => $rows->first()?->closed_at,
                    'kas'        => $rows->map(fn($r) => [
                        'kas_id'      => $r->kas_id,
                        'kas_nama'    => $r->kas?->nama,
                        'saldo_akhir' => (float) $r->saldo_akhir,
                        'is_closed'   => $r->is_closed,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json($periodes);
    }

    public function tutup(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa menutup periode.');
        }

        $request->validate(['periode' => ['required', 'date_format:Y-m']]);

        $this->periodeService->tutupPeriode($request->input('periode') . '-01', $request->user());

        return response()->json(['message' => 'Periode berhasil ditutup.']);
    }

    public function buka(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa membuka periode.');
        }

        $this->periodeService->bukaPeriode($request->user());

        return response()->json(['message' => 'Periode terakhir berhasil dibuka.']);
    }

    public function checkPeriode(Request $request): JsonResponse
    {
        $request->validate(['tanggal' => ['required', 'date']]);
        $tanggal = \Carbon\Carbon::parse($request->input('tanggal'));
        $periode = $tanggal->format('Y-m-01');

        $kasId = $request->input('kas_id');
        $query = SaldoPeriode::where('periode', $periode)->where('is_closed', true);
        if ($kasId) {
            $query->where('kas_id', $kasId);
        }

        return response()->json(['is_closed' => $query->exists()]);
    }
}
