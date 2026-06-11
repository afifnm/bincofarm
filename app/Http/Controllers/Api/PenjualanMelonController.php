<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenjualanMelonRequest;
use App\Http\Resources\PenjualanMelonResource;
use App\Models\ActivityLog;
use App\Models\PenjualanMelon;
use App\Services\PenjualanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PenjualanMelonController extends Controller
{
    public function __construct(private readonly PenjualanService $penjualanService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PenjualanMelon::with(['greenhouse', 'jenisMelon', 'user'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // PJ GH hanya melihat penjualan di GH yang ditugaskan padanya
        if ($request->user()->isPjGh()) {
            $query->whereRelation('greenhouse', 'user_id', $request->user()->id);
        }

        if ($request->filled('greenhouse_id')) {
            $query->where('greenhouse_id', $request->integer('greenhouse_id'));
        }
        if ($request->filled('jenis_melon_id')) {
            $query->where('jenis_melon_id', $request->integer('jenis_melon_id'));
        }
        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->input('sampai'));
        }
        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where('nama_pembeli', 'like', "%{$q}%");
        }

        $perPage = $request->integer('per_page', 20);
        return PenjualanMelonResource::collection($query->paginate($perPage));
    }

    public function store(PenjualanMelonRequest $request): JsonResponse
    {
        $data = $request->validated();
        abort_unless($request->user()->canAccessGreenhouse((int) $data['greenhouse_id']), 403, 'Anda tidak ditugaskan pada greenhouse ini.');
        $data['user_id'] = $request->user()->id;
        $penjualan       = $this->penjualanService->simpan($data);
        $penjualan->load(['greenhouse', 'jenisMelon']);
        ActivityLog::record('create', "Penjualan melon GH #{$penjualan->greenhouse_id} Rp " . number_format((float)$penjualan->total, 0, ',', '.'), $penjualan, [], $request);
        return response()->json(new PenjualanMelonResource($penjualan), 201);
    }

    public function show(Request $request, PenjualanMelon $penjualanMelon): PenjualanMelonResource
    {
        abort_unless($request->user()->canAccessGreenhouse($penjualanMelon->greenhouse_id), 403);
        $penjualanMelon->load(['greenhouse', 'jenisMelon', 'user']);
        return new PenjualanMelonResource($penjualanMelon);
    }

    public function update(PenjualanMelonRequest $request, PenjualanMelon $penjualanMelon): PenjualanMelonResource
    {
        abort_unless($request->user()->canAccessGreenhouse($penjualanMelon->greenhouse_id), 403);
        $data = $request->validated();
        abort_unless($request->user()->canAccessGreenhouse((int) $data['greenhouse_id']), 403, 'Anda tidak ditugaskan pada greenhouse ini.');
        $data['user_id'] = $request->user()->id;
        $penjualan       = $this->penjualanService->update($penjualanMelon, $data);
        $penjualan->load(['greenhouse', 'jenisMelon']);
        ActivityLog::record('update', "Update penjualan melon #{$penjualan->id}", $penjualan, [], $request);
        return new PenjualanMelonResource($penjualan);
    }

    public function destroy(Request $request, PenjualanMelon $penjualanMelon): JsonResponse
    {
        abort_unless($request->user()->canAccessGreenhouse($penjualanMelon->greenhouse_id), 403);
        ActivityLog::record('delete', "Hapus penjualan melon #{$penjualanMelon->id}", $penjualanMelon);
        $this->penjualanService->hapus($penjualanMelon);
        return response()->json(['message' => 'Penjualan dihapus dan transaksi kas di-void.']);
    }
}
