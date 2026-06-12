@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Skeleton loading --}}
    <div id="skeleton">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($i=0;$i<3;$i++)
            <div class="skeleton rounded-2xl h-32"></div>
            @endfor
        </div>
        <div class="skeleton rounded-2xl h-20 mt-4"></div>
    </div>

    {{-- Dashboard PJ GH --}}
    <div id="pjGhSection" class="hidden space-y-6">

        {{-- Ringkasan bulan ini --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="icon-wrap icon-wrap-emerald">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--color-text);">Rekap Bulan Ini</h2>
                    <p class="text-xs" id="periodeInfo" style="color:var(--color-text-muted);"></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="stat-card">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="icon-wrap icon-wrap-emerald" style="width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"/></svg>
                        </div>
                        <p class="text-xs font-medium" style="color:var(--color-text-muted);">Total Panen</p>
                    </div>
                    <p class="text-2xl font-bold" id="panenBulanIni" style="color:var(--color-primary);">— kg</p>
                </div>
                <div class="stat-card">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="icon-wrap icon-wrap-blue" style="width:32px;height:32px;border-radius:8px;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272"/></svg>
                        </div>
                        <p class="text-xs font-medium" style="color:var(--color-text-muted);">Total Terjual</p>
                    </div>
                    <p class="text-2xl font-bold" id="jualBulanIni" style="color:#2563EB;">— kg</p>
                </div>
            </div>
        </div>

        {{-- Daftar GH --}}
        <div id="ghDaftarSection" class="hidden">
            <div class="flex items-center gap-2 mb-4">
                <div class="icon-wrap" style="background:#EDE9FE;">
                    <svg class="w-5 h-5" fill="none" stroke="#7C3AED" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--color-text);">Greenhouse Saya</h2>
                    <p class="text-xs" style="color:var(--color-text-muted);">Status populasi pohon terkini</p>
                </div>
            </div>
            <div id="ghDaftarCards" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>
        </div>

        {{-- Riwayat Populasi Pohon --}}
        <div id="ghHistoriSection" class="hidden">
            <div class="flex items-center gap-2 mb-4">
                <div class="icon-wrap icon-wrap-blue">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--color-text);">Riwayat Perubahan Pohon</h2>
                    <p class="text-xs" style="color:var(--color-text-muted);">10 perubahan terbaru</p>
                </div>
            </div>
            <div class="table-wrap">
                <table class="w-full text-sm">
                    <thead class="tbl-head">
                        <tr>
                            <th>Greenhouse</th>
                            <th class="text-right">Hidup</th>
                            <th class="text-right">Mati</th>
                            <th class="hidden md:table-cell">Catatan</th>
                            <th class="hidden md:table-cell">Oleh</th>
                            <th class="hidden sm:table-cell text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="ghHistoriTbl"></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Saldo Kas (admin/inventory) --}}
    <div id="adminSection" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="icon-wrap icon-wrap-emerald">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--color-text);">Saldo Kas</h2>
                    <p class="text-xs" style="color:var(--color-text-muted);">Ringkasan per akun kas</p>
                </div>
            </div>
        </div>
        <div id="kasCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
    </div>

    {{-- Stok Menipis Alert --}}
    <div id="stokSection" class="hidden">
        <div class="flex items-center gap-2 mb-4">
            <div class="icon-wrap icon-wrap-rose">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold" style="color:var(--color-danger);">Stok Menipis</h2>
                <p class="text-xs" style="color:var(--color-text-muted);">Barang dengan stok di bawah minimum</p>
            </div>
        </div>
        <div class="table-wrap">
            <table class="w-full text-sm">
                <thead class="tbl-head">
                    <tr>
                        <th>Barang</th>
                        <th class="text-right">Stok Saat Ini</th>
                        <th class="text-right">Stok Minimum</th>
                    </tr>
                </thead>
                <tbody id="stokTbl"></tbody>
            </table>
        </div>
    </div>

</div>

<script>
const isPjGh = @json(auth()->user()->isPjGh());
let dashData = { kas: [], stok_menipis: [], greenhouse: null };

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatRp(n) {
    return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}
function fmtKg(n) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits:2, maximumFractionDigits:2 });
}
function fmtDateTime(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
        + ' ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
}

const KAS_SVG = {
    tunai: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>`,
    bank: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>`,
    ewallet: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/></svg>`,
};

function renderPjGh() {
    const gh = dashData.greenhouse || {};
    document.getElementById('periodeInfo').textContent = dashData.periode_info?.bulan || '';
    document.getElementById('panenBulanIni').textContent = fmtKg(gh.panen_bulan_ini_kg) + ' kg';
    document.getElementById('jualBulanIni').textContent = fmtKg(gh.jual_bulan_ini_kg) + ' kg';

    const daftar = gh.daftar || [];
    if (daftar.length) {
        document.getElementById('ghDaftarSection').classList.remove('hidden');
        document.getElementById('ghDaftarCards').innerHTML = daftar.map(g => `
            <div class="stat-card">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--color-text);">${escHtml(g.nama)}</p>
                        <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(g.lokasi || '—')}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs" style="color:var(--color-text-muted);">Panen bulan ini</p>
                        <p class="text-sm font-bold" style="color:var(--color-primary);">${fmtKg(g.panen_bulan_ini)} kg</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-3 border-t" style="border-color:var(--color-border);">
                    <div class="text-center">
                        <p class="text-xs" style="color:var(--color-text-muted);">Total</p>
                        <p class="text-base font-bold" style="color:var(--color-text);">${escHtml(g.total_pohon)}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs" style="color:var(--color-text-muted);">Hidup</p>
                        <p class="text-base font-bold" style="color:#059669;">${escHtml(g.pohon_hidup)}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs" style="color:var(--color-text-muted);">Mati</p>
                        <p class="text-base font-bold" style="color:#DC2626;">${escHtml(g.pohon_mati)}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    const histori = gh.histori_populasi || [];
    if (histori.length) {
        document.getElementById('ghHistoriSection').classList.remove('hidden');
        document.getElementById('ghHistoriTbl').innerHTML = histori.map(h => `
            <tr class="tbl-row">
                <td class="tbl-cell text-sm font-medium" style="color:var(--color-text);">${escHtml(h.greenhouse_nama)}</td>
                <td class="tbl-cell text-right font-semibold" style="color:#059669;">${escHtml(h.pohon_hidup_baru)}</td>
                <td class="tbl-cell text-right font-semibold" style="color:#DC2626;">${escHtml(h.pohon_mati_baru)}</td>
                <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(h.catatan || '—')}</td>
                <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(h.user_nama || '—')}</td>
                <td class="tbl-cell hidden sm:table-cell text-right text-xs" style="color:var(--color-text-muted);">${fmtDateTime(h.created_at)}</td>
            </tr>
        `).join('');
    }
}

function renderAdmin() {
    const kas = dashData.kas || [];
    document.getElementById('kasCards').innerHTML = kas.map(item => {
        const tipe = item.kas.tipe || 'tunai';
        const iconClass = tipe === 'tunai' ? 'icon-wrap-emerald' : tipe === 'bank' ? 'icon-wrap-blue' : 'icon-wrap-purple';
        return `
            <div class="stat-card">
                <div class="flex items-start justify-between mb-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium mb-0.5" style="color:var(--color-text-muted);">${escHtml(item.kas.tipe_label)}</p>
                        <p class="text-sm font-semibold truncate" style="color:var(--color-text);">${escHtml(item.kas.nama)}</p>
                    </div>
                    <div class="icon-wrap ml-3 shrink-0 ${iconClass}">${KAS_SVG[tipe] || KAS_SVG.tunai}</div>
                </div>
                <p class="text-xl font-bold mb-3" style="color:var(--color-primary);">${formatRp(item.kas.saldo_berjalan)}</p>
                <div class="flex items-center gap-4 pt-3 border-t" style="border-color:var(--color-border);">
                    <div class="flex items-center gap-1.5 text-xs">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#DCFCE7;">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="#16A34A" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                        </div>
                        <span style="color:#16A34A;font-weight:500;">${formatRp(item.total_masuk)}</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#FEE2E2;">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="#DC2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                        </div>
                        <span style="color:#DC2626;font-weight:500;">${formatRp(item.total_keluar)}</span>
                    </div>
                </div>
            </div>`;
    }).join('');

    const stok = dashData.stok_menipis || [];
    if (stok.length) {
        document.getElementById('stokSection').classList.remove('hidden');
        document.getElementById('stokTbl').innerHTML = stok.map(b => `
            <tr class="tbl-row">
                <td class="tbl-cell">
                    <div class="flex items-center gap-2">
                        <div class="icon-wrap icon-wrap-rose" style="width:32px;height:32px;border-radius:8px;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                        <div>
                            <p class="font-medium" style="color:var(--color-text);">${escHtml(b.nama)}</p>
                            <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(b.kode)}</p>
                        </div>
                    </div>
                </td>
                <td class="tbl-cell text-right">
                    <span class="badge badge-danger">${escHtml(b.stok)} ${escHtml(b.satuan)}</span>
                </td>
                <td class="tbl-cell text-right text-xs" style="color:var(--color-text-muted);">${escHtml(b.stok_minimum)} ${escHtml(b.satuan)}</td>
            </tr>
        `).join('');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const res = await apiFetch('/api/dashboard');
    if (res?.ok) dashData = await res.json();
    document.getElementById('skeleton').classList.add('hidden');
    if (isPjGh) {
        document.getElementById('pjGhSection').classList.remove('hidden');
        renderPjGh();
    } else {
        document.getElementById('adminSection').classList.remove('hidden');
        renderAdmin();
    }
});
</script>
@endsection
