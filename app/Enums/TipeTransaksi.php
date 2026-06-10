<?php

declare(strict_types=1);

namespace App\Enums;

enum TipeTransaksi: string
{
    case Masuk           = 'masuk';
    case Keluar          = 'keluar';
    case TransferMasuk   = 'transfer_masuk';
    case TransferKeluar  = 'transfer_keluar';

    public function label(): string
    {
        return match($this) {
            self::Masuk          => 'Masuk',
            self::Keluar         => 'Keluar',
            self::TransferMasuk  => 'Transfer Masuk',
            self::TransferKeluar => 'Transfer Keluar',
        };
    }

    public function isDebit(): bool
    {
        return match($this) {
            self::Masuk, self::TransferMasuk => true,
            default => false,
        };
    }
}
