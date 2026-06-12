@extends('layouts.app')
@section('title', 'Transaksi Kas')

@section('content')

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Transaksi Kas</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Catat pemasukan, pengeluaran, dan transfer</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button id="btnCashflow" onclick="goCashflow()" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Cashflow
            </button>
            <button id="btnMasuk" onclick="openCreate('masuk')" class="btn btn-sm btn-success">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                Masuk
            </button>
            <button id="btnKeluar" onclick="openCreate('keluar')" class="btn btn-sm btn-danger">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                Keluar
            </button>
            <button id="btnTransfer" onclick="openTransfer()" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Transfer
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 p-4 rounded-xl border" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-40">
                <label class="form-label">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" id="search" placeholder="Nomor / keterangan..." class="form-input pl-9"/>
                </div>
            </div>
            <div class="flex-1 min-w-36">
                <label class="form-label">Kas</label>
                <select id="filterKas" onchange="load()" class="form-input">
                    <option value="">Semua Kas</option>
                </select>
            </div>
            <div class="flex-1 min-w-32">
                <label class="form-label">Dari</label>
                <input type="date" id="filterDari" class="form-input"/>
            </div>
            <div class="flex-1 min-w-32">
                <label class="form-label">Sampai</label>
                <input type="date" id="filterSampai" class="form-input"/>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5 mt-3">
            <button type="button" id="btnThisMonth" onclick="setThisMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Bulan Ini
            </button>
            <button type="button" id="btnLastMonth" onclick="setLastMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Bulan Lalu
            </button>
            <button type="button" id="btnReset" onclick="resetDates()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset
            </button>
            <button type="button" id="btnToggleVoid" onclick="toggleVoid()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Void
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-14"></div>@endfor
    </div>

    {{-- Table --}}
    <div id="tableContainer" class="hidden table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Transaksi</th>
                    <th class="hidden md:table-cell">Kas</th>
                    <th class="hidden lg:table-cell">Input Oleh</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="pagination" class="hidden flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);"></div>
    </div>

    {{-- Modal Transaksi --}}
    <div id="formModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('formModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Transaksi Masuk</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="saveTrx(event)" class="space-y-4">
                    <div id="formPeriodWarning" class="hidden flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs" style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Periode ini sudah ditutup. Transaksi akan ditolak.
                    </div>
                    <div>
                        <label class="form-label">Kas</label>
                        <select id="fKasId" required class="form-input"></select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input type="date" id="fTanggal" required class="form-input"/>
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select id="fKategoriId" class="form-input">
                            <option value="">— Pilih Kategori —</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" id="fJumlah" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="form-label">Keterangan</label>
                        <input type="text" id="fKeterangan" class="form-input" placeholder="Opsional"/>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn flex-1 justify-center text-white" style="background:var(--color-success);">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Transfer --}}
    <div id="transferModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('transferModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Transfer Antar Kas</h3>
                <button type="button" onclick="closeModal('transferModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="transferForm" onsubmit="saveTransfer(event)" class="space-y-4">
                    <div id="trfPeriodWarning" class="hidden flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs" style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Periode ini sudah ditutup.
                    </div>
                    <div>
                        <label class="form-label">Kas Asal</label>
                        <select id="trfKasAsal" required class="form-input"></select>
                    </div>
                    <div>
                        <label class="form-label">Kas Tujuan</label>
                        <select id="trfKasTujuan" required class="form-input"></select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input type="date" id="trfTanggal" required class="form-input"/>
                    </div>
                    <div>
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" id="trfJumlah" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="form-label">Keterangan</label>
                        <input type="text" id="trfKeterangan" class="form-input" placeholder="Opsional"/>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" onclick="closeModal('transferModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnTransferSave" class="btn btn-primary flex-1 justify-center">Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Void --}}
    <div id="voidModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('voidModal')"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl" style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Void Transaksi</h3>
                <button type="button" onclick="closeModal('voidModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm mb-5" style="color:var(--color-text);">Void transaksi <strong id="voidTargetNomor"></strong>? Saldo akan dikembalikan.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('voidModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="button" id="btnVoid" onclick="doVoid()" class="btn btn-danger flex-1 justify-center">Void</button>
                </div>
            </div>
        </div>
    </div>

<script>
const app = {
    items: [], loading: true, formTipe: 'masuk', voidTarget: null, filterVoid: false,
    kasList: [], kategoriList: [],
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatRp(n) { return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

function sumberInfo(type) {
    const base = (type||'').split('\\').pop();
    const map = { 'PenjualanMelon': { label:'Penjualan Melon', url:'/penjualan-melon' }, 'MutasiBarang': { label:'Mutasi Barang', url:'/mutasi-barang' } };
    return map[base] || { label:base, url:'#' };
}

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableContainer').classList.toggle('hidden', v);
}

function render() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/></svg>
            <p class="text-sm font-medium">Tidak ada transaksi</p></td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    const isMasuk = trx => ['masuk','transfer_masuk'].includes(trx.tipe);
    const svgUp = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>`;
    const svgDn = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>`;
    tbody.innerHTML = app.items.map((trx, idx) => {
        const masuk = isMasuk(trx);
        let aksiHtml = '';
        if (!trx.is_void && !trx.sumber_type) {
            aksiHtml = `<button onclick="openVoidById(${trx.id})" title="Void" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </button>`;
        } else if (!trx.is_void && trx.sumber_type) {
            const si = sumberInfo(trx.sumber_type);
            aksiHtml = `<a href="${si.url}" title="Berasal dari ${escHtml(si.label)}. Batalkan dari halaman ${escHtml(si.label)}." class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </a>`;
        }
        return `<tr class="tbl-row${trx.is_void?' void-row':''}">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ${masuk?'icon-wrap-emerald':'icon-wrap-rose'}">${masuk?svgUp:svgDn}</div>
                    <div>
                        <p class="font-semibold text-xs" style="color:var(--color-text);">${escHtml(trx.nomor)}</p>
                        <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(formatDate(trx.tanggal)+(trx.keterangan?' — '+trx.keterangan:''))}</p>
                    </div>
                </div>
            </td>
            <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);">
                <div>${escHtml(trx.kas?.nama||'-')}</div>
                ${trx.kategori ? `<div class="badge badge-neutral mt-0.5">${escHtml(trx.kategori.nama)}</div>` : ''}
            </td>
            <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(trx.user?.name||'—')}</td>
            <td class="tbl-cell text-right font-bold text-sm" style="color:${masuk?'var(--color-success)':'var(--color-danger)'};">${masuk?'+':'-'}${formatRp(trx.jumlah)}</td>
            <td class="tbl-cell text-center">${trx.is_void ? '<span class="badge badge-danger">VOID</span>' : '<span class="badge badge-success">Aktif</span>'}</td>
            <td class="tbl-cell"><div class="flex items-center justify-center">${aksiHtml}</div></td>
        </tr>`;
    }).join('');
    renderPagination();
}

function buildPageRange(cur, last) {
    if (last <= 7) return Array.from({length:last},(_,i)=>i+1);
    const pages = [];
    if (cur<=4){for(let i=1;i<=5;i++) pages.push(i);pages.push('...');pages.push(last);}
    else if (cur>=last-3){pages.push(1);pages.push('...');for(let i=last-4;i<=last;i++) pages.push(i);}
    else{pages.push(1);pages.push('...');pages.push(cur-1);pages.push(cur);pages.push(cur+1);pages.push('...');pages.push(last);}
    return pages;
}

function renderPagination() {
    const pag = document.getElementById('pagination');
    const m = app.meta;
    if (m.last_page <= 1) { pag.classList.add('hidden'); return; }
    pag.classList.remove('hidden');
    const pageRange = buildPageRange(m.current_page, m.last_page);
    pag.innerHTML = `
        <p class="text-xs" style="color:var(--color-text-muted);">${m.from}–${m.to} dari ${m.total}</p>
        <div class="flex gap-1">
            <button onclick="goPage(${m.current_page-1})" ${m.current_page<=1?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            ${pageRange.map(p => p==='...'
                ? `<button disabled class="w-8 h-8 flex items-center justify-center rounded-lg text-xs" style="border:1px solid var(--color-border);">...</button>`
                : `<button onclick="goPage(${p})" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs" style="${p===m.current_page?'background:var(--color-primary);color:#fff;':'border:1px solid var(--color-border);'}">${p}</button>`
            ).join('')}
            <button onclick="goPage(${m.current_page+1})" ${m.current_page>=m.last_page?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>`;
}

async function load(page = 1) {
    setLoading(true);
    let url = `/api/transaksi-kas?page=${page}&per_page=20`;
    const kasId   = document.getElementById('filterKas').value;
    const dari    = document.getElementById('filterDari').value;
    const sampai  = document.getElementById('filterSampai').value;
    const search  = document.getElementById('search').value;
    if (kasId)  url += `&kas_id=${kasId}`;
    if (dari)   url += `&dari=${dari}`;
    if (sampai) url += `&sampai=${sampai}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (app.filterVoid) url += `&hanya_void=1`;
    const res = await apiFetch(url);
    if (res?.ok) {
        const d = await res.json();
        app.items = d.data || [];
        app.meta = d.meta || { current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
    }
    setLoading(false);
    render();
}

function goPage(p) { if (p !== '...' && p >= 1 && p <= app.meta.last_page) load(p); }

function setThisMonth() { const n=new Date(); document.getElementById('filterDari').value=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); document.getElementById('filterSampai').value=n.toISOString().slice(0,10); load(); }
function setLastMonth() { const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; document.getElementById('filterDari').value=new Date(y,m,1).toISOString().slice(0,10); document.getElementById('filterSampai').value=new Date(y,m+1,0).toISOString().slice(0,10); load(); }
function resetDates() { document.getElementById('filterDari').value=''; document.getElementById('filterSampai').value=''; load(); }

function toggleVoid() {
    app.filterVoid = !app.filterVoid;
    const btn = document.getElementById('btnToggleVoid');
    btn.style.cssText = app.filterVoid ? 'background:#FEE2E2;color:#B91C1C;border-color:#FECACA;font-weight:600;' : '';
    ['btnCashflow','btnMasuk','btnKeluar','btnTransfer','btnThisMonth','btnLastMonth','btnReset'].forEach(id => {
        document.getElementById(id)?.classList.toggle('hidden', app.filterVoid);
    });
    load();
}

function populateKasSelects(...ids) {
    ids.forEach(id => {
        const sel = document.getElementById(id);
        const prev = sel.value;
        sel.innerHTML = '';
        app.kasList.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.nama;
            sel.appendChild(opt);
        });
        if (prev) sel.value = prev;
    });
}

function populateKategoriSelect(tipe) {
    const sel = document.getElementById('fKategoriId');
    sel.innerHTML = '<option value="">— Pilih Kategori —</option>';
    app.kategoriList.filter(k => k.jenis === tipe).forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = k.nama;
        sel.appendChild(opt);
    });
}

function openCreate(tipe) {
    app.formTipe = tipe;
    document.getElementById('formModalTitle').textContent = tipe === 'masuk' ? 'Transaksi Masuk' : 'Transaksi Keluar';
    document.getElementById('btnSave').style.background = tipe === 'masuk' ? 'var(--color-success)' : 'var(--color-danger)';
    document.getElementById('formPeriodWarning').classList.add('hidden');
    populateKasSelects('fKasId');
    if (app.kasList.length) document.getElementById('fKasId').value = app.kasList[0].id;
    populateKategoriSelect(tipe);
    document.getElementById('fTanggal').value = new Date().toISOString().slice(0,10);
    document.getElementById('fJumlah').value = '';
    document.getElementById('fKeterangan').value = '';
    openModal('formModal');
}

function openTransfer() {
    document.getElementById('trfPeriodWarning').classList.add('hidden');
    populateKasSelects('trfKasAsal','trfKasTujuan');
    if (app.kasList.length) document.getElementById('trfKasAsal').value = app.kasList[0].id;
    if (app.kasList.length > 1) document.getElementById('trfKasTujuan').value = app.kasList[1].id;
    document.getElementById('trfTanggal').value = new Date().toISOString().slice(0,10);
    document.getElementById('trfJumlah').value = '';
    document.getElementById('trfKeterangan').value = '';
    openModal('transferModal');
}

async function checkPeriodeForm() {
    const tanggal = document.getElementById('fTanggal').value;
    const kasId   = document.getElementById('fKasId').value;
    if (!tanggal) return;
    const res = await apiFetch(`/api/periode/check?tanggal=${tanggal}&kas_id=${kasId}`);
    if (res?.ok) { const d = await res.json(); document.getElementById('formPeriodWarning').classList.toggle('hidden', !d.is_closed); }
}

async function checkPeriodeTrf() {
    const tanggal = document.getElementById('trfTanggal').value;
    const kasId   = document.getElementById('trfKasAsal').value;
    if (!tanggal) return;
    const res = await apiFetch(`/api/periode/check?tanggal=${tanggal}&kas_id=${kasId}`);
    if (res?.ok) { const d = await res.json(); document.getElementById('trfPeriodWarning').classList.toggle('hidden', !d.is_closed); }
}

async function saveTrx(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        tipe:        app.formTipe,
        kas_id:      document.getElementById('fKasId').value,
        tanggal:     document.getElementById('fTanggal').value,
        kategori_id: document.getElementById('fKategoriId').value,
        jumlah:      parseFloat(document.getElementById('fJumlah').value)||0,
        keterangan:  document.getElementById('fKeterangan').value,
    };
    const res  = await apiFetch('/api/transaksi-kas', { method:'POST', body:JSON.stringify(form) });
    const data = await res.json();
    if (res.ok) {
        toast('Transaksi berhasil.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function saveTransfer(e) {
    e.preventDefault();
    const btn = document.getElementById('btnTransferSave');
    btn.disabled = true; btn.textContent = 'Memproses...';
    const form = {
        kas_asal_id:  document.getElementById('trfKasAsal').value,
        kas_tujuan_id: document.getElementById('trfKasTujuan').value,
        tanggal:      document.getElementById('trfTanggal').value,
        jumlah:       parseFloat(document.getElementById('trfJumlah').value)||0,
        keterangan:   document.getElementById('trfKeterangan').value,
    };
    const res  = await apiFetch('/api/transaksi-kas/transfer', { method:'POST', body:JSON.stringify(form) });
    const data = await res.json();
    if (res.ok) {
        toast('Transfer berhasil.', 'success');
        closeModal('transferModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Transfer';
}

function openVoidById(id) {
    const trx = app.items.find(i => i.id === id);
    if (!trx) return;
    app.voidTarget = trx;
    document.getElementById('voidTargetNomor').textContent = trx.nomor;
    openModal('voidModal');
}

async function doVoid() {
    const btn = document.getElementById('btnVoid');
    btn.disabled = true; btn.textContent = 'Memproses...';
    const res  = await apiFetch(`/api/transaksi-kas/${app.voidTarget.id}`, { method:'DELETE' });
    const data = await res.json();
    if (res.ok) {
        toast('Transaksi di-void.', 'success');
        closeModal('voidModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Void';
}

function goCashflow() {
    const dari   = document.getElementById('filterDari').value;
    const sampai = document.getElementById('filterSampai').value;
    const kasId  = document.getElementById('filterKas').value;
    let url = '/cashflow?';
    if (dari)   url += `dari=${dari}&`;
    if (sampai) url += `sampai=${sampai}&`;
    if (kasId)  url += `kas_id=${kasId}&`;
    window.open(url.replace(/&$/, ''), '_blank');
}

document.getElementById('fTanggal').addEventListener('change', checkPeriodeForm);
document.getElementById('trfTanggal').addEventListener('change', checkPeriodeTrf);
document.getElementById('filterDari').addEventListener('change', () => load());
document.getElementById('filterSampai').addEventListener('change', () => load());
let _searchTimer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => load(), 300);
});

async function init() {
    const now = new Date();
    document.getElementById('filterDari').value  = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('filterSampai').value = now.toISOString().slice(0,10);
    const [kRes, katRes] = await Promise.all([apiFetch('/api/kas?per_page=100'), apiFetch('/api/kategori-transaksi?per_page=100')]);
    if (kRes?.ok)   { const d = await kRes.json();   app.kasList       = d.data || []; }
    if (katRes?.ok) { const d = await katRes.json(); app.kategoriList  = d.data || []; }
    const filterKasSel = document.getElementById('filterKas');
    app.kasList.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = k.nama;
        filterKasSel.appendChild(opt);
    });
    await load();
}

document.addEventListener('DOMContentLoaded', () => init());
</script>
@endsection
