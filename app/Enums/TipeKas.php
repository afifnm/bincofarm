<?php

declare(strict_types=1);

namespace App\Enums;

enum TipeKas: string
{
    case Tunai   = 'tunai';
    case Bank    = 'bank';
    case Ewallet = 'ewallet';

    public function label(): string
    {
        return match($this) {
            self::Tunai   => 'Tunai',
            self::Bank    => 'Bank',
            self::Ewallet => 'E-Wallet',
        };
    }
}
