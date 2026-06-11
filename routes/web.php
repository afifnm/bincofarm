<?php

declare(strict_types=1);

use App\Http\Controllers\Web\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [PageController::class, 'login'])->name('login');

// Semua role (cukup login)
Route::middleware('auth')->group(function (): void {
    Route::get('/',       [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil', [PageController::class, 'profil'])->name('profil');
});

// Admin only
Route::middleware(['auth', 'role:admin'])->group(function (): void {
    // Master Kas (dengan sub menu)
    Route::get('/master-kas/rekening', [PageController::class, 'kas'])->name('kas');
    Route::get('/master-kas/kategori', [PageController::class, 'kategori'])->name('kategori');

    Route::get('/barang',     [PageController::class, 'barang'])->name('barang');
    Route::get('/tutup-buku', [PageController::class, 'tutupBuku'])->name('tutup-buku');

    // Master Jenis Melon
    Route::get('/greenhouse/jenis-melon', [PageController::class, 'jenisMelon'])->name('jenis-melon');

    // User management
    Route::get('/manajemen/user', [PageController::class, 'user'])->name('user');

    // Sistem log
    Route::get('/log-sistem', [PageController::class, 'logSistem'])->name('log-sistem');
});

// Admin + Inventory & Kas
Route::middleware(['auth', 'role:admin,inventory'])->group(function (): void {
    Route::get('/transaksi-kas', [PageController::class, 'transaksiKas'])->name('transaksi-kas');
    Route::get('/mutasi-barang', [PageController::class, 'mutasiBarang'])->name('mutasi-barang');
    Route::get('/cashflow',      [PageController::class, 'cashflow'])->name('cashflow');
    Route::get('/kartu-stok',    [PageController::class, 'kartuStok'])->name('kartu-stok');
});

// Admin + Penanggung Jawab GH
Route::middleware(['auth', 'role:admin,pj_gh'])->group(function (): void {
    // Master GH untuk admin; PJ GH hanya lihat GH-nya & update populasi
    Route::get('/greenhouse',         [PageController::class, 'greenhouse'])->name('greenhouse');
    Route::get('/panen-melon',        [PageController::class, 'panenMelon'])->name('panen-melon');
    Route::get('/penjualan-melon',    [PageController::class, 'penjualanMelon'])->name('penjualan-melon');
    Route::get('/laporan-greenhouse', [PageController::class, 'laporanGreenhouse'])->name('laporan-greenhouse');
});
