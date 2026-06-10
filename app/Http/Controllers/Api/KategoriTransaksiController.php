<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriTransaksiRequest;
use App\Http\Resources\KategoriTransaksiResource;
use App\Models\KategoriTransaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KategoriTransaksiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = KategoriTransaksi::orderBy('jenis')->orderBy('nama')->paginate(100);
        return KategoriTransaksiResource::collection($items);
    }

    public function store(KategoriTransaksiRequest $request): JsonResponse
    {
        $kategori = KategoriTransaksi::create($request->validated());
        return response()->json(new KategoriTransaksiResource($kategori), 201);
    }

    public function show(KategoriTransaksi $kategoriTransaksi): KategoriTransaksiResource
    {
        return new KategoriTransaksiResource($kategoriTransaksi);
    }

    public function update(KategoriTransaksiRequest $request, KategoriTransaksi $kategoriTransaksi): KategoriTransaksiResource
    {
        $kategoriTransaksi->update($request->validated());
        return new KategoriTransaksiResource($kategoriTransaksi);
    }

    public function destroy(KategoriTransaksi $kategoriTransaksi): JsonResponse
    {
        $kategoriTransaksi->delete();
        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
