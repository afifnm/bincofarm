<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulasiPohonHistori extends Model
{
    public $timestamps = false;

    protected $table = 'populasi_pohon_histori';

    protected $fillable = [
        'greenhouse_id',
        'user_id',
        'total_pohon_lama',
        'pohon_hidup_lama',
        'pohon_mati_lama',
        'total_pohon_baru',
        'pohon_hidup_baru',
        'pohon_mati_baru',
        'catatan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_pohon_lama' => 'integer',
            'pohon_hidup_lama' => 'integer',
            'pohon_mati_lama'  => 'integer',
            'total_pohon_baru' => 'integer',
            'pohon_hidup_baru' => 'integer',
            'pohon_mati_baru'  => 'integer',
            'created_at'       => 'datetime',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
