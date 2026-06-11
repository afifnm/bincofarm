<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function login(): View
    {
        return view('pages.login');
    }

    public function dashboard(): View
    {
        return view('pages.dashboard');
    }

    public function kas(): View
    {
        return view('pages.kas');
    }

    public function kategori(): View
    {
        return view('pages.kategori');
    }

    public function transaksiKas(): View
    {
        return view('pages.transaksi-kas');
    }

    public function barang(): View
    {
        return view('pages.barang');
    }

    public function mutasiBarang(): View
    {
        return view('pages.mutasi-barang');
    }

    public function cashflow(): View
    {
        return view('pages.cashflow');
    }

    public function kartuStok(): View
    {
        return view('pages.kartu-stok');
    }

    public function tutupBuku(): View
    {
        return view('pages.tutup-buku');
    }

    public function profil(): View
    {
        return view('pages.profil');
    }

    public function logSistem(): View
    {
        return view('pages.log-sistem');
    }

    public function user(): View
    {
        return view('pages.user');
    }

    public function greenhouse(): View
    {
        return view('pages.greenhouse');
    }

    public function jenisMelon(): View
    {
        return view('pages.jenis-melon');
    }

    public function panenMelon(): View
    {
        return view('pages.panen-melon');
    }

    public function penjualanMelon(): View
    {
        return view('pages.penjualan-melon');
    }

    public function laporanGreenhouse(): View
    {
        return view('pages.laporan-greenhouse');
    }
}
