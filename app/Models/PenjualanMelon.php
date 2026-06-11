<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanMelon extends Model
{
    use SoftDeletes;

    protected $table = 'penjualan_melon';

    protected $fillable = [
        'no_nota',
        'greenhouse_id',
        'nama_pembeli',
        'total',
        'tanggal',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'total'   => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PenjualanMelonItem::class, 'penjualan_melon_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaksiKas(): MorphOne
    {
        return $this->morphOne(TransaksiKas::class, 'sumber');
    }
}
