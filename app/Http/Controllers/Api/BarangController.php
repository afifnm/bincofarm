<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangRequest;
use App\Http\Resources\BarangResource;
use App\Models\ActivityLog;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BarangController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Barang::orderBy('nama');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(fn ($s) => $s->where('nama', 'like', "%{$q}%")
                ->orWhere('kode', 'like', "%{$q}%"));
        }

        $perPage = $request->integer('per_page', 15);
        return BarangResource::collection($query->paginate($perPage));
    }

    public function store(BarangRequest $request): JsonResponse
    {
        $barang = Barang::create($request->validated());
        ActivityLog::record('create', "Tambah barang: {$barang->nama} ({$barang->kode})", $barang, [], $request);
        return response()->json(new BarangResource($barang), 201);
    }

    public function show(Barang $barang): BarangResource
    {
        return new BarangResource($barang);
    }

    public function update(BarangRequest $request, Barang $barang): BarangResource
    {
        $data = $request->validated();
        unset($data['stok']);
        $barang->update($data);
        ActivityLog::record('update', "Update barang: {$barang->nama} ({$barang->kode})", $barang, [], $request);
        return new BarangResource($barang);
    }

    public function destroy(Barang $barang): JsonResponse
    {
        ActivityLog::record('delete', "Hapus barang: {$barang->nama} ({$barang->kode})", $barang);
        $barang->delete();
        return response()->json(['message' => 'Barang dihapus.']);
    }
}
