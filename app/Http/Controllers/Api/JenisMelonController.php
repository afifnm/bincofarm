<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JenisMelonRequest;
use App\Http\Resources\JenisMelonResource;
use App\Models\ActivityLog;
use App\Models\JenisMelon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JenisMelonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = JenisMelon::orderBy('nama');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where('nama', 'like', "%{$q}%");
        }
        if ($request->boolean('semua', false)) {
            return JenisMelonResource::collection($query->where('is_active', true)->get());
        }

        $perPage = $request->integer('per_page', 20);
        return JenisMelonResource::collection($query->paginate($perPage));
    }

    public function store(JenisMelonRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Greenhouse::class);
        $jenis = JenisMelon::create($request->validated());
        ActivityLog::record('create', "Tambah jenis melon: {$jenis->nama}", $jenis, [], $request);
        return response()->json(new JenisMelonResource($jenis), 201);
    }

    public function show(JenisMelon $jenisMelon): JenisMelonResource
    {
        return new JenisMelonResource($jenisMelon);
    }

    public function update(JenisMelonRequest $request, JenisMelon $jenisMelon): JenisMelonResource
    {
        $this->authorize('create', \App\Models\Greenhouse::class);
        $jenisMelon->update($request->validated());
        ActivityLog::record('update', "Update jenis melon: {$jenisMelon->nama}", $jenisMelon, [], $request);
        return new JenisMelonResource($jenisMelon);
    }

    public function destroy(JenisMelon $jenisMelon): JsonResponse
    {
        $this->authorize('create', \App\Models\Greenhouse::class);
        ActivityLog::record('delete', "Hapus jenis melon: {$jenisMelon->nama}", $jenisMelon);
        $jenisMelon->delete();
        return response()->json(['message' => 'Jenis melon dihapus.']);
    }
}
