<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PopulasiPohonRequest;
use App\Http\Resources\GreenhouseResource;
use App\Models\ActivityLog;
use App\Models\Greenhouse;
use App\Models\PopulasiPohonHistori;
use App\Services\PopulasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PopulasiPohonController extends Controller
{
    public function __construct(private readonly PopulasiService $populasiService) {}

    public function update(PopulasiPohonRequest $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorize('manage', $greenhouse);
        $populasi = $this->populasiService->update($greenhouse, $request->validated(), $request->user()->id);
        ActivityLog::record('update', "Update populasi pohon GH {$greenhouse->nama}: total={$populasi->total_pohon}", $greenhouse, [], $request);
        return response()->json([
            'message'  => 'Populasi diperbarui.',
            'populasi' => [
                'total_pohon' => $populasi->total_pohon,
                'pohon_hidup' => $populasi->pohon_hidup,
                'pohon_mati'  => $populasi->pohon_mati,
            ],
        ]);
    }

    public function histori(Request $request, Greenhouse $greenhouse): AnonymousResourceCollection
    {
        $this->authorize('view', $greenhouse);
        $histori = PopulasiPohonHistori::where('greenhouse_id', $greenhouse->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return \Illuminate\Http\Resources\Json\JsonResource::collection($histori);
    }
}
