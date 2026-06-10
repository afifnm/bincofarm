<?php

declare(strict_types=1);

namespace App\Enums;

enum TipeMutasi: string
{
    case Masuk        = 'masuk';
    case Keluar       = 'keluar';
    case Penyesuaian  = 'penyesuaian';

    public function label(): string
    {
        return match($this) {
            self::Masuk       => 'Masuk',
            self::Keluar      => 'Keluar',
            self::Penyesuaian => 'Penyesuaian',
        };
    }

    public function nomorPrefix(): string
    {
        return match($this) {
            self::Masuk       => 'IN',
            self::Keluar      => 'OUT',
            self::Penyesuaian => 'ADJ',
        };
    }
}
