<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopulasiPohon extends Model
{
    protected $table = 'populasi_pohon';

    protected $fillable = [
        'greenhouse_id',
        'total_pohon',
        'pohon_hidup',
        'pohon_mati',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_pohon' => 'integer',
            'pohon_hidup' => 'integer',
            'pohon_mati'  => 'integer',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
