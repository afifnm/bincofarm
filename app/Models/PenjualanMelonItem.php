<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenjualanMelonItem extends Model
{
    protected $table = 'penjualan_melon_item';

    protected $fillable = [
        'penjualan_melon_id',
        'jenis_melon_id',
        'jumlah_kg',
        'harga_per_kg',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_kg'    => 'decimal:2',
            'harga_per_kg' => 'decimal:2',
            'subtotal'     => 'decimal:2',
        ];
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(PenjualanMelon::class, 'penjualan_melon_id');
    }

    public function jenisMelon(): BelongsTo
    {
        return $this->belongsTo(JenisMelon::class);
    }
}
