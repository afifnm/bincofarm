@extends('layouts.app')
@section('title', 'Mutasi Barang')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
.select2-container { width: 100% !important; }
.select2-container--default .select2-selection--single {
    background: var(--color-bg) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: 10px !important;
    height: 41px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--color-text) !important;
    line-height: 39px !important;
    padding-left: 14px !important;
    padding-right: 30px !important;
    font-size: 0.875rem !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: var(--color-text-muted) !important;
    opacity: 0.7;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 39px !important;
    right: 8px !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
    border-color: var(--color-primary) !important;
    box-shadow: 0 0 0 3px rgba(5,150,105,.12) !important;
    outline: none !important;
}
.select2-dropdown {
    background: var(--color-surface) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 24px rgba(0,0,0,.12) !important;
    z-index: 9999 !important;
    overflow: hidden;
}
.select2-search--dropdown { padding: 8px !important; }
.select2-search--dropdown .select2-search__field {
    background: var(--color-bg) !important;
    border: 1px solid var(--color-border) !important;
    border-radius: 8px !important;
    color: var(--color-text) !important;
    font-size: 0.875rem !important;
    padding: 7px 10px !important;
    outline: none !important;
}
.select2-search--dropdown .select2-search__field:focus { border-color: var(--color-primary) !important; }
.select2-results__options { padding: 4px !important; }
.select2-results__option {
    color: var(--color-text) !important;
    font-size: 0.875rem !important;
    padding: 8px 10px !important;
    border-radius: 6px !important;
}
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
    background: var(--color-primary-soft) !important;
    color: var(--color-primary) !important;
}
.select2-container--default .select2-results__option--selected {
    background: var(--color-primary-soft) !important;
    color: var(--color-primary) !important;
    font-weight: 500 !important;
}
.select2-container--default .select2-selection--single .select2-selection__clear {
    color: var(--color-text-muted) !important;
    font-size: 1rem !important;
    font-weight: 400 !important;
    margin-right: 2px !important;
}
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Mutasi Barang</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Pencatatan keluar masuk dan penyesuaian stok</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button id="btnMasuk" onclick="openCreate('masuk')" class="btn btn-sm btn-success">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                Masuk
            </button>
            <button id="btnKeluar" onclick="openCreate('keluar')" class="btn btn-sm btn-danger">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                Keluar
            </button>
            <button id="btnSesuaian" onclick="openCreate('penyesuaian')" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                Sesuaian
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
                <label class="form-label">Barang</label>
                <select id="s2-filter-barang" class="form-input">
                    <option value="">Semua Barang</option>
                </select>
            </div>
            <div class="flex-1 min-w-28">
                <label class="form-label">Dari</label>
                <input type="date" id="filterDari" class="form-input"/>
            </div>
            <div class="flex-1 min-w-28">
                <label class="form-label">Sampai</label>
                <input type="date" id="filterSampai" class="form-input"/>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5 mt-3">
            <button type="button" onclick="setThisMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Bulan Ini
            </button>
            <button type="button" onclick="setLastMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Bulan Lalu
            </button>
            <button type="button" id="btnResetDates" onclick="resetDates()" class="filter-pill text-xs px-3 py-1">
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
                    <th>Mutasi</th>
                    <th class="hidden md:table-cell">Barang</th>
                    <th class="hidden lg:table-cell">Input Oleh</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right hidden md:table-cell">Stok Sisa</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div id="pagination" class="hidden flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);"></div>
    </div>

    {{-- Modal Form --}}
    <div id="formModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('formModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Barang Masuk</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="saveMutasi(event)" class="space-y-4">
                    <div id="periodWarning" class="hidden flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs" style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Periode ini sudah ditutup.
                    </div>
                    <div>
                        <label class="form-label">Barang</label>
                        <select id="s2-form-barang" class="form-input"></select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input type="date" id="fTanggal" required class="form-input"/>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label id="fQtyLabel" class="form-label">Qty</label>
                            <input type="number" id="fQty" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                        </div>
                        <div>
                            <label class="form-label">Harga Satuan (Rp)</label>
                            <input type="number" id="fHargaSatuan" min="0" step="0.01" class="form-input" placeholder="0"/>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Referensi (No. Faktur)</label>
                        <input type="text" id="fReferensi" class="form-input" placeholder="Opsional"/>
                    </div>
                    <div>
                        <label class="form-label">Keterangan</label>
                        <input type="text" id="fKeterangan" class="form-input" placeholder="Opsional"/>
                    </div>
                    {{-- Link to Kas --}}
                    <div id="linkKasSection" class="border-t pt-4" style="border-color:var(--color-border);">
                        <div class="flex items-center gap-2 mb-3">
                            <input type="checkbox" id="linkKas" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer" onchange="toggleLinkKas()"/>
                            <label for="linkKas" class="text-sm cursor-pointer" style="color:var(--color-text);">Buat transaksi kas otomatis</label>
                        </div>
                        <div id="linkKasSelect" class="hidden">
                            <label class="form-label">Pilih Kas</label>
                            <select id="fKasId" class="form-input">
                                <option value="">— Pilih Kas —</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn flex-1 justify-center text-white" style="background:var(--color-success);">Simpan</button>
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
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Void Mutasi</h3>
                <button type="button" onclick="closeModal('voidModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm mb-5" style="color:var(--color-text);">Void mutasi <strong id="voidTargetNomor"></strong>? Stok akan dikembalikan.</p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('voidModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="button" id="btnVoid" onclick="doVoid()" class="btn btn-danger flex-1 justify-center">Void</button>
                </div>
            </div>
        </div>
    </div>

<script>
const app = {
    items: [], loading: true, formTipe: 'masuk', voidTarget: null,
    barangList: [], kasList: [], filterVoid: false, _s2: false,
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableContainer').classList.toggle('hidden', v);
}

function render() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
            <p class="text-sm font-medium">Tidak ada mutasi</p></td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    const svgMasuk = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>`;
    const svgKeluar = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>`;
    const svgSesuai = `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>`;
    tbody.innerHTML = app.items.map((m, idx) => {
        const iconClass = m.tipe==='masuk' ? 'icon-wrap-emerald' : m.tipe==='keluar' ? 'icon-wrap-rose' : 'icon-wrap-blue';
        const iconSvg = m.tipe==='masuk' ? svgMasuk : m.tipe==='keluar' ? svgKeluar : svgSesuai;
        const qtyColor = m.tipe==='masuk'?'var(--color-success)':m.tipe==='keluar'?'var(--color-danger)':'#0369A1';
        const qtyPrefix = m.tipe==='masuk'?'+':m.tipe==='keluar'?'-':'±';
        const voidBtn = !m.is_void ? `<button onclick="openVoidById(${m.id})" title="Void" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        </button>` : '';
        return `<tr class="tbl-row${m.is_void?' void-row':''}">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 ${iconClass}">${iconSvg}</div>
                    <div>
                        <p class="font-semibold text-xs" style="color:var(--color-text);">${escHtml(m.nomor)}</p>
                        <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(formatDate(m.tanggal)+(m.keterangan?' — '+m.keterangan:''))}</p>
                        <p class="text-xs md:hidden mt-0.5 font-medium" style="color:var(--color-text);">${escHtml(m.barang?.nama||'-')}</p>
                    </div>
                </div>
            </td>
            <td class="tbl-cell hidden md:table-cell text-xs">
                <p class="font-medium" style="color:var(--color-text);">${escHtml(m.barang?.nama||'-')}</p>
                ${m.referensi?`<p style="color:var(--color-text-muted);">Ref: ${escHtml(m.referensi)}</p>`:''}
            </td>
            <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(m.user?.name||'—')}</td>
            <td class="tbl-cell text-right font-bold text-sm" style="color:${qtyColor};">${qtyPrefix}${escHtml(String(m.qty))} ${escHtml(m.barang?.satuan||'')}</td>
            <td class="tbl-cell text-right hidden md:table-cell text-xs font-semibold" style="color:var(--color-text);">${escHtml(String(m.stok_setelah))} ${escHtml(m.barang?.satuan||'')}</td>
            <td class="tbl-cell text-center">
                ${m.is_void ? '<span class="badge badge-danger">VOID</span>' : '<span class="badge badge-success">Aktif</span>'}
            </td>
            <td class="tbl-cell"><div class="flex items-center justify-center">${voidBtn}</div></td>
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
    let url = `/api/mutasi-barang?page=${page}&per_page=20`;
    const filterBarang = app._s2 ? ($('#s2-filter-barang').val()||'') : '';
    const dari = document.getElementById('filterDari').value;
    const sampai = document.getElementById('filterSampai').value;
    const search = document.getElementById('search').value;
    if (filterBarang) url += `&barang_id=${filterBarang}`;
    if (dari) url += `&dari=${dari}`;
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
function resetDates() {
    document.getElementById('filterDari').value = '';
    document.getElementById('filterSampai').value = '';
    document.getElementById('search').value = '';
    if (app._s2) { $('#s2-filter-barang').val(null).trigger('change'); } else { load(); }
}

function toggleVoid() {
    app.filterVoid = !app.filterVoid;
    const btn = document.getElementById('btnToggleVoid');
    btn.style.cssText = app.filterVoid ? 'background:#FEE2E2;color:#B91C1C;border-color:#FECACA;font-weight:600;' : '';
    const createBtns = ['btnMasuk','btnKeluar','btnSesuaian','btnResetDates'];
    createBtns.forEach(id => document.getElementById(id)?.classList.toggle('hidden', app.filterVoid));
    load();
}

function toggleLinkKas() {
    document.getElementById('linkKasSelect').classList.toggle('hidden', !document.getElementById('linkKas').checked);
}

function populateKasOptions() {
    const sel = document.getElementById('fKasId');
    sel.innerHTML = '<option value="">— Pilih Kas —</option>';
    app.kasList.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = k.nama;
        sel.appendChild(opt);
    });
}

function openCreate(tipe) {
    app.formTipe = tipe;
    const titles = { masuk: 'Barang Masuk', keluar: 'Barang Keluar', penyesuaian: 'Penyesuaian Stok' };
    const btnColors = { masuk: 'var(--color-success)', keluar: 'var(--color-danger)', penyesuaian: '#0369A1' };
    document.getElementById('formModalTitle').textContent = titles[tipe];
    document.getElementById('fQtyLabel').textContent = tipe === 'penyesuaian' ? 'Stok Baru' : 'Qty';
    document.getElementById('btnSave').style.background = btnColors[tipe];
    document.getElementById('fTanggal').value = new Date().toISOString().slice(0,10);
    document.getElementById('fQty').value = '';
    document.getElementById('fHargaSatuan').value = 0;
    document.getElementById('fReferensi').value = '';
    document.getElementById('fKeterangan').value = '';
    document.getElementById('linkKas').checked = false;
    document.getElementById('linkKasSelect').classList.add('hidden');
    document.getElementById('periodWarning').classList.add('hidden');
    document.getElementById('linkKasSection').classList.toggle('hidden', tipe === 'penyesuaian');
    populateKasOptions();
    const defId = app.barangList[0]?.id || '';
    if (app._s2) {
        setTimeout(() => $('#s2-form-barang').val(defId).trigger('change'), 0);
    } else {
        document.getElementById('s2-form-barang').value = defId;
    }
    openModal('formModal');
}

function openVoidById(id) {
    const m = app.items.find(i => i.id === id);
    if (!m) return;
    app.voidTarget = m;
    document.getElementById('voidTargetNomor').textContent = m.nomor;
    openModal('voidModal');
}

async function checkPeriode() {
    const tanggal = document.getElementById('fTanggal').value;
    if (!tanggal) return;
    const res = await apiFetch(`/api/periode/check?tanggal=${tanggal}`);
    if (res?.ok) {
        const d = await res.json();
        document.getElementById('periodWarning').classList.toggle('hidden', !d.is_closed);
    }
}

async function saveMutasi(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const linkKas = document.getElementById('linkKas').checked;
    const payload = {
        tipe:          app.formTipe,
        barang_id:     app._s2 ? ($('#s2-form-barang').val()||'') : document.getElementById('s2-form-barang').value,
        tanggal:       document.getElementById('fTanggal').value,
        qty:           parseFloat(document.getElementById('fQty').value)||0,
        harga_satuan:  parseFloat(document.getElementById('fHargaSatuan').value)||0,
        referensi:     document.getElementById('fReferensi').value,
        keterangan:    document.getElementById('fKeterangan').value,
    };
    if (linkKas) payload.kas_id = document.getElementById('fKasId').value;
    const res  = await apiFetch('/api/mutasi-barang', { method:'POST', body:JSON.stringify(payload) });
    const data = await res.json();
    if (res.ok) {
        toast('Mutasi berhasil.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function doVoid() {
    const btn = document.getElementById('btnVoid');
    btn.disabled = true; btn.textContent = 'Memproses...';
    const res  = await apiFetch(`/api/mutasi-barang/${app.voidTarget.id}`, { method:'DELETE' });
    const data = await res.json();
    if (res.ok) {
        toast('Mutasi di-void.', 'success');
        closeModal('voidModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message, 'error');
    }
    btn.disabled = false; btn.textContent = 'Void';
}

function initS2() {
    const self = app;
    $('#s2-filter-barang').select2({
        placeholder: 'Semua Barang', allowClear: true, width: '100%', dropdownParent: $('body')
    }).on('change', function() { load(); });
    $('#s2-form-barang').select2({
        placeholder: 'Pilih Barang', width: '100%', dropdownParent: $('body')
    });
    self._s2 = true;
}

async function init() {
    const now = new Date();
    document.getElementById('filterDari').value = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
    document.getElementById('filterSampai').value = now.toISOString().slice(0,10);

    const [bRes, kRes] = await Promise.all([apiFetch('/api/barang?per_page=200'), apiFetch('/api/kas?per_page=100')]);
    if (bRes?.ok) { const d = await bRes.json(); app.barangList = d.data || []; }
    if (kRes?.ok) { const d = await kRes.json(); app.kasList = d.data || []; }

    // Populate filter select options
    const filterSel = document.getElementById('s2-filter-barang');
    app.barangList.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = b.nama;
        filterSel.appendChild(opt);
    });

    // Populate form barang select options
    const formSel = document.getElementById('s2-form-barang');
    app.barangList.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = `${b.nama} (stok: ${b.stok} ${b.satuan})`;
        formSel.appendChild(opt);
    });

    await load();
    initS2();
}

document.getElementById('fTanggal').addEventListener('change', checkPeriode);
document.getElementById('filterDari').addEventListener('change', () => load());
document.getElementById('filterSampai').addEventListener('change', () => load());
let _searchTimer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => load(), 300);
});

document.addEventListener('DOMContentLoaded', () => init());
</script>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush
@endsection
