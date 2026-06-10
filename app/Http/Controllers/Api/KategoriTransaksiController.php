<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriTransaksiRequest;
use App\Http\Resources\KategoriTransaksiResource;
use App\Models\ActivityLog;
use App\Models\KategoriTransaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KategoriTransaksiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = KategoriTransaksi::orderBy('jenis')->orderBy('nama');

        if (request()->filled('search')) {
            $q = request()->input('search');
            $query->where('nama', 'like', "%{$q}%");
        }
        if (request()->filled('jenis')) {
            $query->where('jenis', request()->input('jenis'));
        }

        $perPage = request()->integer('per_page', 100);
        $items = $query->paginate($perPage);
        return KategoriTransaksiResource::collection($items);
    }

    public function store(KategoriTransaksiRequest $request): JsonResponse
    {
        $kategori = KategoriTransaksi::create($request->validated());
        ActivityLog::record('create', "Tambah kategori: {$kategori->nama} ({$kategori->jenis->value})", $kategori, [], $request);
        return response()->json(new KategoriTransaksiResource($kategori), 201);
    }

    public function show(KategoriTransaksi $kategoriTransaksi): KategoriTransaksiResource
    {
        return new KategoriTransaksiResource($kategoriTransaksi);
    }

    public function update(KategoriTransaksiRequest $request, KategoriTransaksi $kategoriTransaksi): KategoriTransaksiResource
    {
        $kategoriTransaksi->update($request->validated());
        ActivityLog::record('update', "Update kategori: {$kategoriTransaksi->nama}", $kategoriTransaksi, [], $request);
        return new KategoriTransaksiResource($kategoriTransaksi);
    }

    public function destroy(KategoriTransaksi $kategoriTransaksi): JsonResponse
    {
        ActivityLog::record('delete', "Hapus kategori: {$kategoriTransaksi->nama}", $kategoriTransaksi);
        $kategoriTransaksi->delete();
        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
