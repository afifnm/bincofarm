<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriTransaksiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nama'      => $this->nama,
            'jenis'     => $this->jenis->value,
            'jenis_label'=> $this->jenis->label(),
            'is_active' => $this->is_active,
            'in_use'    => ($this->transaksi_kas_count ?? 0) > 0,
        ];
    }
}
