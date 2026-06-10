<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoPeriode extends Model
{
    protected $table = 'saldo_periode';

    protected $fillable = [
        'kas_id',
        'periode',
        'saldo_akhir',
        'total_masuk',
        'total_keluar',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'periode'     => 'date',
            'saldo_akhir' => 'decimal:2',
            'total_masuk' => 'decimal:2',
            'total_keluar'=> 'decimal:2',
            'is_closed'   => 'boolean',
            'closed_at'   => 'datetime',
        ];
    }

    public function kas(): BelongsTo
    {
        return $this->belongsTo(Kas::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
