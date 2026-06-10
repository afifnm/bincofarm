<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokPeriode extends Model
{
    protected $table = 'stok_periode';

    protected $fillable = [
        'barang_id',
        'periode',
        'stok_akhir',
        'total_masuk',
        'total_keluar',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'periode'     => 'date',
            'stok_akhir'  => 'decimal:2',
            'total_masuk' => 'decimal:2',
            'total_keluar'=> 'decimal:2',
            'is_closed'   => 'boolean',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
