<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PanenMelonRequest;
use App\Http\Resources\PanenMelonResource;
use App\Models\ActivityLog;
use App\Models\Greenhouse;
use App\Models\PanenMelon;
use App\Models\SaldoPeriode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PanenMelonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PanenMelon::with(['greenhouse', 'jenisMelon', 'user'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // PJ GH hanya melihat panen di GH yang ditugaskan padanya
        if ($request->user()->isPjGh()) {
            $query->whereRelation('greenhouse', 'user_id', $request->user()->id);
        }

        if ($request->filled('greenhouse_id')) {
            $query->where('greenhouse_id', $request->integer('greenhouse_id'));
        }
        if ($request->filled('jenis_melon_id')) {
            $query->where('jenis_melon_id', $request->integer('jenis_melon_id'));
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }
        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->input('sampai'));
        }

        $perPage = $request->integer('per_page', 20);
        return PanenMelonResource::collection($query->paginate($perPage));
    }

    public function store(PanenMelonRequest $request): JsonResponse
    {
        $data = $request->validated();
        abort_unless($request->user()->canAccessGreenhouse((int) $data['greenhouse_id']), 403, 'Anda tidak ditugaskan pada greenhouse ini.');
        $this->assertPeriodeTerbuka((int) $data['greenhouse_id'], Carbon::parse($data['tanggal']));
        $data['user_id'] = $request->user()->id;
        $panen           = PanenMelon::create($data);
        $panen->load(['greenhouse', 'jenisMelon']);
        ActivityLog::record('create', "Catat panen GH #{$panen->greenhouse_id} {$panen->berat}kg grade {$panen->grade->value}", $panen, [], $request);
        return response()->json(new PanenMelonResource($panen), 201);
    }

    public function show(Request $request, PanenMelon $panenMelon): PanenMelonResource
    {
        abort_unless($request->user()->canAccessGreenhouse($panenMelon->greenhouse_id), 403);
        $panenMelon->load(['greenhouse', 'jenisMelon', 'user']);
        return new PanenMelonResource($panenMelon);
    }

    public function update(PanenMelonRequest $request, PanenMelon $panenMelon): PanenMelonResource
    {
        abort_unless($request->user()->canAccessGreenhouse($panenMelon->greenhouse_id), 403);
        $data = $request->validated();
        abort_unless($request->user()->canAccessGreenhouse((int) $data['greenhouse_id']), 403, 'Anda tidak ditugaskan pada greenhouse ini.');
        $this->assertPeriodeTerbuka((int) $data['greenhouse_id'], Carbon::parse($data['tanggal']));
        $data['user_id'] = $request->user()->id;
        $panenMelon->update($data);
        $panenMelon->load(['greenhouse', 'jenisMelon']);
        ActivityLog::record('update', "Update panen #{$panenMelon->id}", $panenMelon, [], $request);
        return new PanenMelonResource($panenMelon);
    }

    public function destroy(Request $request, PanenMelon $panenMelon): JsonResponse
    {
        abort_unless($request->user()->canAccessGreenhouse($panenMelon->greenhouse_id), 403);
        $this->assertPeriodeTerbuka($panenMelon->greenhouse_id, Carbon::parse($panenMelon->tanggal));
        ActivityLog::record('delete', "Hapus panen #{$panenMelon->id}", $panenMelon);
        $panenMelon->delete();
        return response()->json(['message' => 'Data panen dihapus.']);
    }

    private function assertPeriodeTerbuka(int $greenhouseId, Carbon $tanggal): void
    {
        $gh      = Greenhouse::find($greenhouseId);
        $kasId   = $gh?->kas_id;
        $periode = Carbon::create($tanggal->year, $tanggal->month, 1)->toDateString();

        $closed = SaldoPeriode::where('is_closed', true)
            ->where('periode', $periode)
            ->when($kasId, fn ($q) => $q->where('kas_id', $kasId))
            ->exists();

        if ($closed) {
            abort(409, "Periode {$tanggal->format('Y-m')} sudah ditutup. Data panen tidak dapat diubah.");
        }
    }
}
