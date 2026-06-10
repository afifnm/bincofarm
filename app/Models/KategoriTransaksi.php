<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JenisKategori;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriTransaksi extends Model
{
    protected $table = 'kategori_transaksi';

    protected $fillable = ['nama', 'jenis', 'is_active'];

    protected function casts(): array
    {
        return [
            'jenis'     => JenisKategori::class,
            'is_active' => 'boolean',
        ];
    }

    public function transaksiKas(): HasMany
    {
        return $this->hasMany(TransaksiKas::class, 'kategori_id');
    }
}
