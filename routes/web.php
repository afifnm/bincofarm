<?php

declare(strict_types=1);

use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [PageController::class, 'login'])->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/',              [PageController::class, 'dashboard'])->name('dashboard');

    // Master Kas (dengan sub menu)
    Route::get('/master-kas/rekening', [PageController::class, 'kas'])->name('kas');
    Route::get('/master-kas/kategori', [PageController::class, 'kategori'])->name('kategori');

    Route::get('/transaksi-kas', [PageController::class, 'transaksiKas'])->name('transaksi-kas');
    Route::get('/barang',        [PageController::class, 'barang'])->name('barang');
    Route::get('/mutasi-barang', [PageController::class, 'mutasiBarang'])->name('mutasi-barang');
    Route::get('/cashflow',      [PageController::class, 'cashflow'])->name('cashflow');
    Route::get('/kartu-stok',    [PageController::class, 'kartuStok'])->name('kartu-stok');
    Route::get('/tutup-buku',    [PageController::class, 'tutupBuku'])->name('tutup-buku');

    // User profile
    Route::get('/profil',        [PageController::class, 'profil'])->name('profil');

    // Sistem log
    Route::get('/log-sistem',    [PageController::class, 'logSistem'])->name('log-sistem');
});
