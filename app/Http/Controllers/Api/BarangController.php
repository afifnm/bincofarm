<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangRequest;
use App\Http\Resources\BarangResource;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BarangController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $items = Barang::orderBy('nama')->paginate(50);
        return BarangResource::collection($items);
    }

    public function store(BarangRequest $request): JsonResponse
    {
        $barang = Barang::create($request->validated());
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
        return new BarangResource($barang);
    }

    public function destroy(Barang $barang): JsonResponse
    {
        $barang->delete();
        return response()->json(['message' => 'Barang dihapus.']);
    }
}
