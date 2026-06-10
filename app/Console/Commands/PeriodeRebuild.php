<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\Kas;
use App\Services\PeriodeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PeriodeRebuild extends Command
{
    protected $signature = 'periode:rebuild
                            {--kas=     : ID kas (kosong = semua kas)}
                            {--barang=  : ID barang (kosong = semua barang)}
                            {--dari=    : Dari periode YYYY-MM (kosong = awal data)}';

    protected $description = 'Rebuild semua snapshot saldo/stok periode dari ledger (source of truth)';

    public function handle(PeriodeService $service): int
    {
        $kasId    = $this->option('kas')    ? (int) $this->option('kas')    : null;
        $barangId = $this->option('barang') ? (int) $this->option('barang') : null;
        $dari     = $this->option('dari')   ? $this->option('dari') . '-01' : '2000-01-01';

        $kasCollection = $kasId
            ? Kas::where('id', $kasId)->get()
            : Kas::all();

        foreach ($kasCollection as $kas) {
            $this->line("Recalc kas: {$kas->nama} ({$kas->kode})");
            $service->recalcKasSince($kas->id, $dari);
        }

        $barangCollection = $barangId
            ? Barang::where('id', $barangId)->get()
            : Barang::all();

        foreach ($barangCollection as $barang) {
            $this->line("Recalc barang: {$barang->nama} ({$barang->kode})");
            $service->recalcBarangSince($barang->id, $dari);
        }

        $this->info('Rebuild selesai.');

        return self::SUCCESS;
    }
}
