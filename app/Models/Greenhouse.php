<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Greenhouse extends Model
{
    use SoftDeletes;

    protected $table = 'greenhouses';

    protected $fillable = [
        'nama',
        'lokasi',
        'user_id',
        'kas_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kas(): BelongsTo
    {
        return $this->belongsTo(Kas::class);
    }

    public function populasi(): HasOne
    {
        return $this->hasOne(PopulasiPohon::class);
    }

    public function populasiHistori(): HasMany
    {
        return $this->hasMany(PopulasiPohonHistori::class);
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
