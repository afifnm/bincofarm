<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanenMelonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'greenhouse_id'  => $this->greenhouse_id,
            'greenhouse'     => $this->whenLoaded('greenhouse', fn () => ['id' => $this->greenhouse->id, 'nama' => $this->greenhouse->nama]),
            'jenis_melon_id' => $this->jenis_melon_id,
            'jenis_melon'    => $this->whenLoaded('jenisMelon', fn () => ['id' => $this->jenisMelon->id, 'nama' => $this->jenisMelon->nama]),
            'berat'          => (float) $this->berat,
            'grade'          => $this->grade->value,
            'grade_label'    => $this->grade->label(),
            'is_busuk'       => $this->is_busuk,
            'tanggal'        => $this->tanggal?->toDateString(),
            'user_id'        => $this->user_id,
            'user'           => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name]),
            'created_at'     => $this->created_at,
        ];
    }
}
