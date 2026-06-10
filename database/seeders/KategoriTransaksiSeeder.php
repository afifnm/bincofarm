<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\KategoriTransaksi;
use Illuminate\Database\Seeder;

class KategoriTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Penjualan',        'jenis' => 'masuk'],
            ['nama' => 'Retur Pembelian',  'jenis' => 'masuk'],
            ['nama' => 'Modal Masuk',      'jenis' => 'masuk'],
            ['nama' => 'Penerimaan Lain',  'jenis' => 'masuk'],
            ['nama' => 'Pembelian',        'jenis' => 'keluar'],
            ['nama' => 'Gaji Karyawan',    'jenis' => 'keluar'],
            ['nama' => 'Listrik & Air',    'jenis' => 'keluar'],
            ['nama' => 'Sewa',             'jenis' => 'keluar'],
            ['nama' => 'Operasional',      'jenis' => 'keluar'],
            ['nama' => 'Pengeluaran Lain', 'jenis' => 'keluar'],
        ];

        foreach ($data as $row) {
            KategoriTransaksi::create($row + ['is_active' => true]);
        }
    }
}
