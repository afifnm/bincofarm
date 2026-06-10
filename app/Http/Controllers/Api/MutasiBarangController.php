<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MutasiBarangRequest;
use App\Http\Resources\MutasiBarangResource;
use App\Models\ActivityLog;
use App\Models\MutasiBarang;
use App\Services\BarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MutasiBarangController extends Controller
{
    public function __construct(private readonly BarangService $barangService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MutasiBarang::with(['barang', 'user'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('barang_id')) {
            $query->where('barang_id', $request->integer('barang_id'));
        }
        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->input('sampai'));
        }
        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(fn ($s) => $s->where('nomor', 'like', "%{$q}%")
                ->orWhere('keterangan', 'like', "%{$q}%")
                ->orWhere('referensi', 'like', "%{$q}%"));
        }

        $perPage = $request->integer('per_page', 20);
        return MutasiBarangResource::collection($query->paginate($perPage));
    }

    public function store(MutasiBarangRequest $request): JsonResponse
    {
        $data            = $request->validated();
        $data['user_id'] = $request->user()->id;
        $mutasi          = $this->barangService->createMutasi($data);
        $mutasi->load(['barang', 'user']);

        ActivityLog::record(
            'create',
            "Mutasi barang {$mutasi->tipe->value}: {$mutasi->nomor} - {$mutasi->barang->nama} qty {$mutasi->qty}",
            $mutasi,
            [],
            $request
        );

        return response()->json(new MutasiBarangResource($mutasi), 201);
    }

    public function show(MutasiBarang $mutasiBarang): MutasiBarangResource
    {
        $mutasiBarang->load(['barang', 'user']);
        return new MutasiBarangResource($mutasiBarang);
    }

    public function destroy(MutasiBarang $mutasiBarang): JsonResponse
    {
        ActivityLog::record('void', "Void mutasi barang: {$mutasiBarang->nomor}", $mutasiBarang);
        $this->barangService->voidMutasi($mutasiBarang);
        return response()->json(['message' => 'Mutasi di-void.']);
    }
}
