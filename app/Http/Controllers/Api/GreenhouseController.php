<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GreenhouseRequest;
use App\Http\Resources\GreenhouseResource;
use App\Models\ActivityLog;
use App\Models\Greenhouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GreenhouseController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Greenhouse::with(['user', 'kas', 'populasi'])->orderBy('nama');

        // PJ GH hanya melihat GH yang ditugaskan padanya
        if ($request->user()->isPjGh()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(fn ($s) => $s->where('nama', 'like', "%{$q}%")
                ->orWhere('lokasi', 'like', "%{$q}%"));
        }
        if ($request->filled('kas_id')) {
            $query->where('kas_id', $request->integer('kas_id'));
        }
        if ($request->boolean('semua', false)) {
            return GreenhouseResource::collection($query->where('is_active', true)->get());
        }

        $perPage = $request->integer('per_page', 20);
        return GreenhouseResource::collection($query->paginate($perPage));
    }

    public function store(GreenhouseRequest $request): JsonResponse
    {
        $this->authorize('create', Greenhouse::class);
        $gh = Greenhouse::create($request->validated());
        ActivityLog::record('create', "Tambah greenhouse: {$gh->nama}", $gh, [], $request);
        $gh->load(['user', 'kas']);
        return response()->json(new GreenhouseResource($gh), 201);
    }

    public function show(Greenhouse $greenhouse): GreenhouseResource
    {
        $this->authorize('view', $greenhouse);
        $greenhouse->load(['user', 'kas', 'populasi']);
        return new GreenhouseResource($greenhouse);
    }

    public function update(GreenhouseRequest $request, Greenhouse $greenhouse): GreenhouseResource
    {
        $this->authorize('update', $greenhouse);
        $greenhouse->update($request->validated());
        ActivityLog::record('update', "Update greenhouse: {$greenhouse->nama}", $greenhouse, [], $request);
        $greenhouse->load(['user', 'kas']);
        return new GreenhouseResource($greenhouse);
    }

    public function destroy(Greenhouse $greenhouse): JsonResponse
    {
        $this->authorize('delete', $greenhouse);
        ActivityLog::record('delete', "Hapus greenhouse: {$greenhouse->nama}", $greenhouse);
        $greenhouse->delete();
        return response()->json(['message' => 'Greenhouse dihapus.']);
    }
}
