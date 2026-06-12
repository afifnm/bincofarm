<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanMelonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'no_nota'       => $this->no_nota,
            'greenhouse_id' => $this->greenhouse_id,
            'greenhouse'    => $this->whenLoaded('greenhouse', fn () => ['id' => $this->greenhouse->id, 'nama' => $this->greenhouse->nama, 'lokasi' => $this->greenhouse->lokasi]),
            'nama_pembeli'  => $this->nama_pembeli,
            'items'         => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id'             => $item->id,
                'jenis_melon_id' => $item->jenis_melon_id,
                'jenis_melon'    => $item->relationLoaded('jenisMelon') && $item->jenisMelon
                    ? ['id' => $item->jenisMelon->id, 'nama' => $item->jenisMelon->nama]
                    : null,
                'jumlah_kg'      => (float) $item->jumlah_kg,
                'harga_per_kg'   => (float) $item->harga_per_kg,
                'subtotal'       => (float) $item->subtotal,
            ])->values()),
            'total_kg'      => $this->whenLoaded('items', fn () => (float) $this->items->sum('jumlah_kg')),
            'total'         => (float) $this->total,
            'tanggal'       => $this->tanggal?->toDateString(),
            'user_id'       => $this->user_id,
            'user'          => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name]),
            'created_at'    => $this->created_at,
            'deleted_at'    => $this->deleted_at,
        ];
    }
}
