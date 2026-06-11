<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GreenhouseController;
use App\Http\Controllers\Api\JenisMelonController;
use App\Http\Controllers\Api\KasController;
use App\Http\Controllers\Api\KategoriTransaksiController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\LaporanGreenhouseController;
use App\Http\Controllers\Api\MutasiBarangController;
use App\Http\Controllers\Api\PanenMelonController;
use App\Http\Controllers\Api\PenjualanMelonController;
use App\Http\Controllers\Api\PeriodeController;
use App\Http\Controllers\Api\PopulasiPohonController;
use App\Http\Controllers\Api\TransaksiKasController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// ── Semua role (cukup login) ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function (): void {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // User profile (atur profil sendiri)
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'changePassword']);
});

// ── Admin only ──────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->group(function (): void {
    // Master Kas & Kategori (tulis)
    Route::apiResource('kas', KasController::class)
        ->parameters(['kas' => 'kas'])->only(['store', 'update', 'destroy']);
    Route::apiResource('kategori-transaksi', KategoriTransaksiController::class)
        ->only(['store', 'update', 'destroy']);

    // Master Barang (tulis)
    Route::apiResource('barang', BarangController::class)->only(['store', 'update', 'destroy']);

    // Periode / tutup buku
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::post('/periode/tutup', [PeriodeController::class, 'tutup']);
    Route::post('/periode/buka', [PeriodeController::class, 'buka']);
    Route::get('/periode/check', [PeriodeController::class, 'checkPeriode']);

    // User CRUD (manajemen)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Activity log
    Route::get('/activity-log', [ActivityLogController::class, 'index']);

    // Master Greenhouse & Jenis Melon (tulis)
    Route::apiResource('greenhouse', GreenhouseController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('jenis-melon', JenisMelonController::class)
        ->parameters(['jenis-melon' => 'jenisMelon'])->only(['store', 'update', 'destroy']);
});

// ── Admin + Inventory & Kas ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin,inventory'])->group(function (): void {
    // Master (baca, untuk dropdown form)
    Route::apiResource('kas', KasController::class)
        ->parameters(['kas' => 'kas'])->only(['index', 'show']);
    Route::apiResource('kategori-transaksi', KategoriTransaksiController::class)->only(['index', 'show']);
    Route::apiResource('barang', BarangController::class)->only(['index', 'show']);

    // Transaksi Kas
    Route::post('/transaksi-kas/transfer', [TransaksiKasController::class, 'transfer']);
    Route::get('/transaksi-kas', [TransaksiKasController::class, 'index']);
    Route::post('/transaksi-kas', [TransaksiKasController::class, 'store']);
    Route::get('/transaksi-kas/{transaksiKas}', [TransaksiKasController::class, 'show']);
    Route::delete('/transaksi-kas/{transaksiKas}', [TransaksiKasController::class, 'destroy']);

    // Mutasi Barang
    Route::get('/mutasi-barang', [MutasiBarangController::class, 'index']);
    Route::post('/mutasi-barang', [MutasiBarangController::class, 'store']);
    Route::get('/mutasi-barang/{mutasiBarang}', [MutasiBarangController::class, 'show']);
    Route::delete('/mutasi-barang/{mutasiBarang}', [MutasiBarangController::class, 'destroy']);

    // Laporan keuangan & stok
    Route::get('/laporan/cashflow', [LaporanController::class, 'cashflow']);
    Route::get('/laporan/kartu-stok', [LaporanController::class, 'kartuStok']);
});

// ── Admin + Penanggung Jawab GH (data dibatasi GH yang ditugaskan) ──────────
Route::middleware(['auth:sanctum', 'role:admin,pj_gh'])->group(function (): void {
    // Greenhouse (baca; pj_gh hanya melihat GH miliknya)
    Route::apiResource('greenhouse', GreenhouseController::class)->only(['index', 'show']);

    // Jenis Melon (baca, untuk dropdown form)
    Route::apiResource('jenis-melon', JenisMelonController::class)
        ->parameters(['jenis-melon' => 'jenisMelon'])->only(['index', 'show']);

    // Populasi Pohon (per GH)
    Route::put('/greenhouse/{greenhouse}/populasi', [PopulasiPohonController::class, 'update']);
    Route::get('/greenhouse/{greenhouse}/populasi/histori', [PopulasiPohonController::class, 'histori']);

    // Panen Melon
    Route::get('/panen-melon', [PanenMelonController::class, 'index']);
    Route::post('/panen-melon', [PanenMelonController::class, 'store']);
    Route::get('/panen-melon/{panenMelon}', [PanenMelonController::class, 'show']);
    Route::put('/panen-melon/{panenMelon}', [PanenMelonController::class, 'update']);
    Route::delete('/panen-melon/{panenMelon}', [PanenMelonController::class, 'destroy']);

    // Penjualan Melon
    Route::get('/penjualan-melon', [PenjualanMelonController::class, 'index']);
    Route::post('/penjualan-melon', [PenjualanMelonController::class, 'store']);
    Route::get('/penjualan-melon/{penjualanMelon}', [PenjualanMelonController::class, 'show']);
    Route::put('/penjualan-melon/{penjualanMelon}', [PenjualanMelonController::class, 'update']);
    Route::delete('/penjualan-melon/{penjualanMelon}', [PenjualanMelonController::class, 'destroy']);

    // Laporan GH
    Route::get('/laporan/greenhouse/panen', [LaporanGreenhouseController::class, 'rekapPanen']);
    Route::get('/laporan/greenhouse/perbandingan', [LaporanGreenhouseController::class, 'perbandingan']);
});
