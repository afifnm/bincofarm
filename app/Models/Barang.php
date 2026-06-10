<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli'    => 'decimal:2',
            'harga_jual'    => 'decimal:2',
            'stok'          => 'decimal:2',
            'stok_minimum'  => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    public function mutasiBarang(): HasMany
    {
        return $this->hasMany(MutasiBarang::class);
    }

    public function stokPeriode(): HasMany
    {
        return $this->hasMany(StokPeriode::class);
    }

    public function isStokMenipis(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }
}
