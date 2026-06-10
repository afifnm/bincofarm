<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MutasiBarangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nomor'        => $this->nomor,
            'barang_id'    => $this->barang_id,
            'barang'       => new BarangResource($this->whenLoaded('barang')),
            'tanggal'      => $this->tanggal?->toDateString(),
            'tipe'         => $this->tipe->value,
            'tipe_label'   => $this->tipe->label(),
            'qty'          => (float) $this->qty,
            'harga_satuan' => (float) $this->harga_satuan,
            'stok_setelah' => (float) $this->stok_setelah,
            'referensi'    => $this->referensi,
            'keterangan'   => $this->keterangan,
            'is_void'      => $this->is_void,
            'void_at'      => $this->void_at,
            'user_id'      => $this->user_id,
            'created_at'   => $this->created_at,
        ];
    }
}
