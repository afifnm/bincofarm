<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'kode'          => $this->kode,
            'nama'          => $this->nama,
            'satuan'        => $this->satuan,
            'harga_beli'    => (float) $this->harga_beli,
            'harga_jual'    => (float) $this->harga_jual,
            'stok'          => (float) $this->stok,
            'stok_minimum'  => (float) $this->stok_minimum,
            'stok_menipis'  => $this->isStokMenipis(),
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at,
        ];
    }
}
