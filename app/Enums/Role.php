<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Admin     = 'admin';
    case Inventory = 'inventory';
    case PjGh      = 'pj_gh';

    public function label(): string
    {
        return match ($this) {
            self::Admin     => 'Admin',
            self::Inventory => 'Inventory & Kas',
            self::PjGh      => 'Penanggung Jawab GH',
        };
    }

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
