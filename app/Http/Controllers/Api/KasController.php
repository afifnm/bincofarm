<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasRequest;
use App\Http\Resources\KasResource;
use App\Models\ActivityLog;
use App\Models\Kas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KasController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $query = Kas::orderBy('nama');

        if (request()->filled('search')) {
            $q = request()->input('search');
            $query->where('nama', 'like', "%{$q}%");
        }

        $perPage = request()->integer('per_page', 50);
        $kas = $query->paginate($perPage);
        return KasResource::collection($kas);
    }

    public function store(KasRequest $request): JsonResponse
    {
        $data                   = $request->validated();
        $data['saldo_berjalan'] = $data['saldo_awal'];
        $kas                    = Kas::create($data);
        ActivityLog::record('create', "Tambah rekening kas: {$kas->nama}", $kas, [], $request);
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
        ActivityLog::record('update', "Update rekening kas: {$kas->nama}", $kas, [], $request);
        return new KasResource($kas);
    }

    public function destroy(Kas $kas): JsonResponse
    {
        ActivityLog::record('delete', "Hapus rekening kas: {$kas->nama}", $kas);
        $kas->delete();
        return response()->json(['message' => 'Rekening dihapus.']);
    }
}
