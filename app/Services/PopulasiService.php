<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\PopulasiPohon;
use App\Models\PopulasiPohonHistori;
use Illuminate\Support\Facades\DB;

class PopulasiService
{
    public function update(Greenhouse $greenhouse, array $data, int $userId): PopulasiPohon
    {
        return DB::transaction(function () use ($greenhouse, $data, $userId): PopulasiPohon {
            $current = PopulasiPohon::firstOrNew(['greenhouse_id' => $greenhouse->id]);

            PopulasiPohonHistori::create([
                'greenhouse_id'   => $greenhouse->id,
                'user_id'         => $userId,
                'total_pohon_lama' => $current->total_pohon ?? 0,
                'pohon_hidup_lama' => $current->pohon_hidup ?? 0,
                'pohon_mati_lama'  => $current->pohon_mati ?? 0,
                'total_pohon_baru' => $data['total_pohon'],
                'pohon_hidup_baru' => $data['pohon_hidup'],
                'pohon_mati_baru'  => $data['pohon_mati'],
                'catatan'          => $data['catatan'] ?? null,
                'created_at'       => now(),
            ]);

            $current->fill([
                'greenhouse_id' => $greenhouse->id,
                'total_pohon'   => $data['total_pohon'],
                'pohon_hidup'   => $data['pohon_hidup'],
                'pohon_mati'    => $data['pohon_mati'],
                'updated_by'    => $userId,
            ])->save();

            return $current;
        });
    }
}
