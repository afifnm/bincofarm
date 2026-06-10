<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KasController;
use App\Http\Controllers\Api\KategoriTransaksiController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MutasiBarangController;
use App\Http\Controllers\Api\PeriodeController;
use App\Http\Controllers\Api\TransaksiKasController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function (): void {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Kas
    Route::apiResource('kas', KasController::class);

    // Kategori Transaksi
    Route::apiResource('kategori-transaksi', KategoriTransaksiController::class);

    // Transaksi Kas
    Route::post('/transaksi-kas/transfer', [TransaksiKasController::class, 'transfer']);
    Route::get('/transaksi-kas', [TransaksiKasController::class, 'index']);
    Route::post('/transaksi-kas', [TransaksiKasController::class, 'store']);
    Route::get('/transaksi-kas/{transaksiKas}', [TransaksiKasController::class, 'show']);
    Route::delete('/transaksi-kas/{transaksiKas}', [TransaksiKasController::class, 'destroy']);

    // Barang
    Route::apiResource('barang', BarangController::class);

    // Mutasi Barang
    Route::get('/mutasi-barang', [MutasiBarangController::class, 'index']);
    Route::post('/mutasi-barang', [MutasiBarangController::class, 'store']);
    Route::get('/mutasi-barang/{mutasiBarang}', [MutasiBarangController::class, 'show']);
    Route::delete('/mutasi-barang/{mutasiBarang}', [MutasiBarangController::class, 'destroy']);

    // Laporan
    Route::get('/laporan/cashflow', [LaporanController::class, 'cashflow']);
    Route::get('/laporan/kartu-stok', [LaporanController::class, 'kartuStok']);

    // Periode
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::post('/periode/tutup', [PeriodeController::class, 'tutup']);
    Route::post('/periode/buka', [PeriodeController::class, 'buka']);
    Route::get('/periode/check', [PeriodeController::class, 'checkPeriode']);

    // User profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'changePassword']);

    // Activity log
    Route::get('/activity-log', [ActivityLogController::class, 'index']);
});
