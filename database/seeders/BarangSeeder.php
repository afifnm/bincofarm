<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode'          => 'BRG-001',
                'nama'          => 'Pupuk Urea 50kg',
                'satuan'        => 'Sak',
                'harga_beli'    => 150_000,
                'harga_jual'    => 175_000,
                'stok'          => 100,
                'stok_minimum'  => 20,
                'is_active'     => true,
            ],
            [
                'kode'          => 'BRG-002',
                'nama'          => 'Pestisida Cair 1L',
                'satuan'        => 'Botol',
                'harga_beli'    => 85_000,
                'harga_jual'    => 100_000,
                'stok'          => 50,
                'stok_minimum'  => 10,
                'is_active'     => true,
            ],
            [
                'kode'          => 'BRG-003',
                'nama'          => 'Benih Jagung 5kg',
                'satuan'        => 'Kemasan',
                'harga_beli'    => 120_000,
                'harga_jual'    => 145_000,
                'stok'          => 30,
                'stok_minimum'  => 5,
                'is_active'     => true,
            ],
            [
                'kode'          => 'BRG-004',
                'nama'          => 'Alat Semprot 16L',
                'satuan'        => 'Unit',
                'harga_beli'    => 350_000,
                'harga_jual'    => 420_000,
                'stok'          => 15,
                'stok_minimum'  => 3,
                'is_active'     => true,
            ],
            [
                'kode'          => 'BRG-005',
                'nama'          => 'Pupuk NPK 50kg',
                'satuan'        => 'Sak',
                'harga_beli'    => 280_000,
                'harga_jual'    => 320_000,
                'stok'          => 8,
                'stok_minimum'  => 10,
                'is_active'     => true,
            ],
        ];

        foreach ($data as $row) {
            Barang::create($row);
        }
    }
}
