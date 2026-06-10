<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipeMutasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MutasiBarang extends Model
{
    protected $table = 'mutasi_barang';

    protected $fillable = [
        'nomor',
        'barang_id',
        'tanggal',
        'tipe',
        'qty',
        'harga_satuan',
        'stok_setelah',
        'referensi',
        'keterangan',
        'user_id',
        'is_void',
        'void_at',
    ];

    protected function casts(): array
    {
        return [
            'tipe'         => TipeMutasi::class,
            'tanggal'      => 'date',
            'qty'          => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'stok_setelah' => 'decimal:2',
            'is_void'      => 'boolean',
            'void_at'      => 'datetime',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaksiKas(): MorphMany
    {
        return $this->morphMany(TransaksiKas::class, 'sumber');
    }
}
