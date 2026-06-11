<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Kas;
use Illuminate\Database\Seeder;

class KasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama'           => 'Kas Utama',
                'tipe'           => 'tunai',
                'saldo_awal'     => 5_000_000,
                'saldo_berjalan' => 5_000_000,
                'is_active'      => true,
            ],
            [
                'nama'           => 'Bank BCA',
                'tipe'           => 'bank',
                'saldo_awal'     => 20_000_000,
                'saldo_berjalan' => 20_000_000,
                'is_active'      => true,
            ],
            [
                'nama'           => 'GoPay / OVO',
                'tipe'           => 'ewallet',
                'saldo_awal'     => 1_000_000,
                'saldo_berjalan' => 1_000_000,
                'is_active'      => true,
            ],
        ];

        foreach ($data as $row) {
            Kas::create($row);
        }
    }
}
