<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransaksiKasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'nomor'          => $this->nomor,
            'kas_id'         => $this->kas_id,
            'kas'            => new KasResource($this->whenLoaded('kas')),
            'kategori_id'    => $this->kategori_id,
            'kategori'       => new KategoriTransaksiResource($this->whenLoaded('kategori')),
            'tanggal'        => $this->tanggal?->toDateString(),
            'tipe'           => $this->tipe->value,
            'tipe_label'     => $this->tipe->label(),
            'jumlah'         => (float) $this->jumlah,
            'keterangan'     => $this->keterangan,
            'transfer_group' => $this->transfer_group,
            'is_void'        => $this->is_void,
            'void_at'        => $this->void_at,
            'user_id'        => $this->user_id,
            'created_at'     => $this->created_at,
        ];
    }
}
