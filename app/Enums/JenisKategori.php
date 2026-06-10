<?php

declare(strict_types=1);

namespace App\Enums;

enum JenisKategori: string
{
    case Masuk  = 'masuk';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match($this) {
            self::Masuk  => 'Masuk',
            self::Keluar => 'Keluar',
        };
    }
}
