<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GreenhouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nama'      => $this->nama,
            'lokasi'    => $this->lokasi,
            'user_id'   => $this->user_id,
            'user'      => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name]),
            'kas_id'    => $this->kas_id,
            'kas'       => new KasResource($this->whenLoaded('kas')),
            'is_active' => $this->is_active,
            'populasi'  => $this->whenLoaded('populasi', fn () => [
                'total_pohon' => $this->populasi?->total_pohon ?? 0,
                'pohon_hidup' => $this->populasi?->pohon_hidup ?? 0,
                'pohon_mati'  => $this->populasi?->pohon_mati ?? 0,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
