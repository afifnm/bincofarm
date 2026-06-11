<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipeKas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kas extends Model
{
    use SoftDeletes;

    protected $table = 'kas';

    protected $fillable = [
        'nama',
        'tipe',
        'saldo_awal',
        'saldo_berjalan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tipe'            => TipeKas::class,
            'saldo_awal'      => 'decimal:2',
            'saldo_berjalan'  => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class);
    }

    public function saldoPeriode(): HasMany
    {
        return $this->hasMany(SaldoPeriode::class);
    }

    public function greenhouses(): HasMany
    {
        return $this->hasMany(Greenhouse::class);
    }
}
