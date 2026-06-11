<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiKasRequest;
use App\Http\Requests\TransferKasRequest;
use App\Http\Resources\TransaksiKasResource;
use App\Models\ActivityLog;
use App\Models\TransaksiKas;
use App\Services\KasService;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransaksiKasController extends Controller
{
    public function __construct(
        private readonly KasService      $kasService,
        private readonly TransferService $transferService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = TransaksiKas::with(['kas', 'kategori', 'user'])
            ->whereHas('kas')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('kas_id')) {
            $query->where('kas_id', $request->integer('kas_id'));
        }
        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->input('sampai'));
        }
        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(fn ($s) => $s->where('nomor', 'like', "%{$q}%")
                ->orWhere('keterangan', 'like', "%{$q}%"));
        }
        if ($request->boolean('hanya_void')) {
            $query->where('is_void', true);
        } else {
            $query->where('is_void', false);
        }

        $perPage = $request->integer('per_page', 20);
        return TransaksiKasResource::collection($query->paginate($perPage));
    }

    public function store(TransaksiKasRequest $request): JsonResponse
    {
        $data         = $request->validated();
        $data['user_id'] = $request->user()->id;
        $trx          = $this->kasService->createTransaksi($data);
        $trx->load(['kas', 'kategori']);

        ActivityLog::record('create', "Buat transaksi kas {$trx->nomor} ({$trx->tipe->value}) Rp " . number_format((float)$trx->jumlah, 0, ',', '.'), $trx, [], $request);

        return response()->json(new TransaksiKasResource($trx), 201);
    }

    public function show(TransaksiKas $transaksiKas): TransaksiKasResource
    {
        $transaksiKas->load(['kas', 'kategori']);
        return new TransaksiKasResource($transaksiKas);
    }

    public function destroy(TransaksiKas $transaksiKas): JsonResponse
    {
        if ($transaksiKas->transfer_group) {
            $this->transferService->voidTransfer($transaksiKas->transfer_group);
        } else {
            $this->kasService->voidTransaksi($transaksiKas);
        }

        ActivityLog::record('void', "Void transaksi kas {$transaksiKas->nomor}", $transaksiKas);

        return response()->json(['message' => 'Transaksi di-void.']);
    }

    public function transfer(TransferKasRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        [$trxKeluar, $trxMasuk] = $this->transferService->transfer($data);

        ActivityLog::record('transfer', "Transfer kas {$trxKeluar->nomor} Rp " . number_format((float)$trxKeluar->jumlah, 0, ',', '.'), $trxKeluar, [], $request);

        return response()->json([
            'message'    => 'Transfer berhasil.',
            'trx_keluar' => new TransaksiKasResource($trxKeluar),
            'trx_masuk'  => new TransaksiKasResource($trxMasuk),
        ], 201);
    }
}
