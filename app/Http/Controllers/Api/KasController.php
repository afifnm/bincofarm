<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasRequest;
use App\Http\Resources\KasResource;
use App\Models\Kas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KasController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $kas = Kas::orderBy('nama')->paginate(50);
        return KasResource::collection($kas);
    }

    public function store(KasRequest $request): JsonResponse
    {
        $data       = $request->validated();
        $data['saldo_berjalan'] = $data['saldo_awal'];
        $kas        = Kas::create($data);

        return response()->json(new KasResource($kas), 201);
    }

    public function show(Kas $kas): KasResource
    {
        return new KasResource($kas);
    }

    public function update(KasRequest $request, Kas $kas): KasResource
    {
        $data = $request->validated();
        unset($data['saldo_awal']);
        $kas->update($data);

        return new KasResource($kas);
    }

    public function destroy(Kas $kas): JsonResponse
    {
        $kas->delete();
        return response()->json(['message' => 'Kas dihapus.']);
    }
}
