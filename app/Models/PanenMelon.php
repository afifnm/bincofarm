<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GradeHasil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PanenMelon extends Model
{
    use SoftDeletes;

    protected $table = 'panen_melon';

    protected $fillable = [
        'greenhouse_id',
        'jenis_melon_id',
        'berat',
        'grade',
        'is_busuk',
        'tanggal',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'berat'    => 'decimal:2',
            'grade'    => GradeHasil::class,
            'is_busuk' => 'boolean',
            'tanggal'  => 'date',
        ];
    }

    public function greenhouse(): BelongsTo
    {
        return $this->belongsTo(Greenhouse::class);
    }

    public function jenisMelon(): BelongsTo
    {
        return $this->belongsTo(JenisMelon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
