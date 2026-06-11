<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nama'            => $this->nama,
            'tipe'            => $this->tipe->value,
            'tipe_label'      => $this->tipe->label(),
            'saldo_awal'      => (float) $this->saldo_awal,
            'saldo_berjalan'  => (float) $this->saldo_berjalan,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at,
        ];
    }
}
