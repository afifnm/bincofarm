<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipeTransaksi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransaksiKas extends Model
{
    protected $table = 'transaksi_kas';

    protected $fillable = [
        'nomor',
        'kas_id',
        'kategori_id',
        'tanggal',
        'tipe',
        'jumlah',
        'keterangan',
        'transfer_group',
        'sumber_type',
        'sumber_id',
        'user_id',
        'is_void',
        'void_at',
    ];

    protected function casts(): array
    {
        return [
            'tipe'     => TipeTransaksi::class,
            'tanggal'  => 'date',
            'jumlah'   => 'decimal:2',
            'is_void'  => 'boolean',
            'void_at'  => 'datetime',
        ];
    }

    public function kas(): BelongsTo
    {
        return $this->belongsTo(Kas::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class, 'kategori_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sumber(): MorphTo
    {
        return $this->morphTo();
    }
}
