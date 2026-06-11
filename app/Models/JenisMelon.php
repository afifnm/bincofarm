<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisMelon extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_melon';

    protected $fillable = [
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function panenMelon(): HasMany
    {
        return $this->hasMany(PanenMelon::class);
    }

    public function penjualanMelon(): HasMany
    {
        return $this->hasMany(PenjualanMelon::class);
    }
}
