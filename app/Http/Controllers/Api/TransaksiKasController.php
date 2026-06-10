<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransaksiKasRequest;
use App\Http\Requests\TransferKasRequest;
use App\Http\Resources\TransaksiKasResource;
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
        $query = TransaksiKas::with(['kas', 'kategori'])
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
        if ($request->has('include_void') && $request->boolean('include_void') === false) {
            $query->where('is_void', false);
        }

        return TransaksiKasResource::collection($query->paginate(50));
    }

    public function store(TransaksiKasRequest $request): JsonResponse
    {
        $data         = $request->validated();
        $data['user_id'] = $request->user()->id;
        $trx          = $this->kasService->createTransaksi($data);
        $trx->load(['kas', 'kategori']);

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

        return response()->json(['message' => 'Transaksi di-void.']);
    }

    public function transfer(TransferKasRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        [$trxKeluar, $trxMasuk] = $this->transferService->transfer($data);

        return response()->json([
            'message'    => 'Transfer berhasil.',
            'trx_keluar' => new TransaksiKasResource($trxKeluar),
            'trx_masuk'  => new TransaksiKasResource($trxMasuk),
        ], 201);
    }
}
