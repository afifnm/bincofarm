@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Master Barang</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola data produk dan stok inventori</p>
        </div>
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Barang
        </button>
    </div>

    {{-- Search bar --}}
    <div class="mb-4">
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="search" placeholder="Cari barang..." class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-2xl h-16"></div>@endfor
    </div>

    {{-- Mobile cards --}}
    <div id="mobileCards" class="hidden md:hidden space-y-3"></div>

    {{-- Desktop table --}}
    <div id="tableContainer" class="hidden md:block table-wrap" style="display:none!important">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th class="text-right">Stok</th>
                    <th class="text-right">Harga Beli</th>
                    <th class="text-right">Harga Jual</th>
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
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah Barang</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="save(event)" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Kode (SKU)</label>
                            <input type="text" id="fKode" required class="form-input" placeholder="SKU-001"/>
                        </div>
                        <div>
                            <label class="form-label">Satuan</label>
                            <input type="text" id="fSatuan" required class="form-input" placeholder="kg, pcs, ltr"/>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Nama Barang</label>
                        <input type="text" id="fNama" required class="form-input" placeholder="Nama produk"/>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Harga Beli (Rp)</label>
                            <input type="number" id="fHargaBeli" min="0" step="0.01" class="form-input" placeholder="0"/>
                        </div>
                        <div>
                            <label class="form-label">Harga Jual (Rp)</label>
                            <input type="number" id="fHargaJual" min="0" step="0.01" class="form-input" placeholder="0"/>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Stok Minimum</label>
                        <input type="number" id="fStokMinimum" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="fIsActive" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                        <label for="fIsActive" class="text-sm cursor-pointer" style="color:var(--color-text);">Barang aktif</label>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn btn-primary flex-1 justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('deleteModal')"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl" style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus Barang</h3>
                <button type="button" onclick="closeModal('deleteModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm mb-5" style="color:var(--color-text);">
                    Hapus barang <strong id="deleteTargetName"></strong>? Pastikan tidak ada mutasi yang terkait.
                </p>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="button" id="btnDelete" onclick="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
                </div>
            </div>
        </div>
    </div>

<script>
const app = {
    items: [], loading: true, editId: null,
    deleteTarget: null, saving: false,
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatRp(n) { return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

/* ── Rendering ── */
function render() {
    renderMobile();
    renderDesktop();
}

function renderMobile() {
    const container = document.getElementById('mobileCards');
    if (!app.items.length) {
        container.innerHTML = `<div class="card card-p text-center py-8 text-sm" style="color:var(--color-text-muted);">Tidak ada barang ditemukan</div>`;
        return;
    }
    container.innerHTML = app.items.map(b => `
        <div class="card card-p">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="icon-wrap shrink-0 ${b.stok_menipis ? 'icon-wrap-rose' : 'icon-wrap-emerald'}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:var(--color-text);">${escHtml(b.nama)}</p>
                        <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(b.kode)} · ${escHtml(b.satuan)}</p>
                    </div>
                </div>
                ${b.stok_menipis ? '<span class="badge badge-danger shrink-0 ml-2">Menipis</span>' : ''}
            </div>
            <div class="grid grid-cols-3 gap-2 text-xs py-3 border-t border-b mb-3" style="border-color:var(--color-border);">
                <div>
                    <p class="mb-0.5" style="color:var(--color-text-muted);">Stok</p>
                    <p class="font-bold" style="color:${b.stok_menipis?'var(--color-danger)':'var(--color-text)'};">${escHtml(b.stok)} ${escHtml(b.satuan)}</p>
                </div>
                <div>
                    <p class="mb-0.5" style="color:var(--color-text-muted);">Harga Beli</p>
                    <p class="font-medium" style="color:var(--color-text);">${formatRp(b.harga_beli)}</p>
                </div>
                <div>
                    <p class="mb-0.5" style="color:var(--color-text-muted);">Harga Jual</p>
                    <p class="font-medium" style="color:var(--color-primary);">${formatRp(b.harga_jual)}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="openEditById(${b.id})" class="btn btn-sm btn-secondary flex-1 justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    Edit
                </button>
                <button onclick="openDeleteById(${b.id})" class="btn btn-sm btn-danger flex-1 justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    Hapus
                </button>
            </div>
        </div>
    `).join('');
}

function renderDesktop() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"/></svg>
            <p class="text-sm font-medium">Tidak ada barang ditemukan</p>
        </td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.items.map((b, idx) => `
        <tr class="tbl-row">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell font-mono text-xs" style="color:var(--color-text-muted);">${escHtml(b.kode)}</td>
            <td class="tbl-cell">
                <div class="flex items-center gap-2">
                    <span class="font-medium" style="color:var(--color-text);">${escHtml(b.nama)}</span>
                    ${b.stok_menipis ? '<span class="badge badge-danger">Menipis</span>' : ''}
                </div>
            </td>
            <td class="tbl-cell text-right font-semibold" style="color:${b.stok_menipis?'var(--color-danger)':'var(--color-text)'};">${escHtml(b.stok)} ${escHtml(b.satuan)}</td>
            <td class="tbl-cell text-right text-xs" style="color:var(--color-text-muted);">${formatRp(b.harga_beli)}</td>
            <td class="tbl-cell text-right font-medium" style="color:var(--color-primary);">${formatRp(b.harga_jual)}</td>
            <td class="tbl-cell text-center">
                <span class="badge ${b.is_active ? 'badge-success' : 'badge-neutral'}">${b.is_active ? 'Aktif' : 'Nonaktif'}</span>
            </td>
            <td class="tbl-cell text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="openEditById(${b.id})" title="Edit"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--color-text-muted);"
                            onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                            onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </button>
                    <button onclick="openDeleteById(${b.id})" title="Hapus"
                            class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                            style="color:var(--color-text-muted);"
                            onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'"
                            onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    renderPagination();
}

function renderPagination() {
    const pag = document.getElementById('pagination');
    const m = app.meta;
    if (m.last_page <= 1) { pag.classList.add('hidden'); return; }
    pag.classList.remove('hidden');
    const pageRange = buildPageRange(m.current_page, m.last_page);
    pag.innerHTML = `
        <p class="text-xs" style="color:var(--color-text-muted);">
            Menampilkan ${m.from}–${m.to} dari ${m.total} barang
        </p>
        <div class="flex gap-1">
            <button onclick="goPage(${m.current_page-1})" ${m.current_page<=1?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors disabled:opacity-40" style="border:1px solid var(--color-border);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            ${pageRange.map(p => p==='...'
                ? `<button disabled class="w-8 h-8 flex items-center justify-center rounded-lg text-xs" style="border:1px solid var(--color-border);">...</button>`
                : `<button onclick="goPage(${p})" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors" style="${p===m.current_page?'background:var(--color-primary);color:#fff;':'border:1px solid var(--color-border);'}">${p}</button>`
            ).join('')}
            <button onclick="goPage(${m.current_page+1})" ${m.current_page>=m.last_page?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors disabled:opacity-40" style="border:1px solid var(--color-border);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    `;
}

function buildPageRange(cur, last) {
    if (last <= 7) return Array.from({length:last},(_,i)=>i+1);
    const pages = [];
    if (cur<=4){for(let i=1;i<=5;i++) pages.push(i);pages.push('...');pages.push(last);}
    else if (cur>=last-3){pages.push(1);pages.push('...');for(let i=last-4;i<=last;i++) pages.push(i);}
    else{pages.push(1);pages.push('...');pages.push(cur-1);pages.push(cur);pages.push(cur+1);pages.push('...');pages.push(last);}
    return pages;
}

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    const tc = document.getElementById('tableContainer');
    const mc = document.getElementById('mobileCards');
    tc.style.removeProperty('display');
    tc.classList.toggle('hidden', v);
    mc.classList.toggle('hidden', v);
}

/* ── API ── */
async function load(page = 1) {
    setLoading(true);
    let url = `/api/barang?page=${page}&per_page=15`;
    const s = document.getElementById('search').value;
    if (s) url += `&search=${encodeURIComponent(s)}`;
    const res = await apiFetch(url);
    if (res?.ok) {
        const d = await res.json();
        app.items = d.data || [];
        app.meta = d.meta || { current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
    }
    setLoading(false);
    render();
}

function goPage(p) { if (p>=1 && p<=app.meta.last_page) load(p); }

/* ── CRUD ── */
function openCreate() {
    app.editId = null;
    document.getElementById('formModalTitle').textContent = 'Tambah Barang';
    document.getElementById('fKode').value = '';
    document.getElementById('fNama').value = '';
    document.getElementById('fSatuan').value = '';
    document.getElementById('fHargaBeli').value = 0;
    document.getElementById('fHargaJual').value = 0;
    document.getElementById('fStokMinimum').value = 0;
    document.getElementById('fIsActive').checked = true;
    openModal('formModal');
}

function openEditById(id) {
    const b = app.items.find(i => i.id === id);
    if (!b) return;
    app.editId = b.id;
    document.getElementById('formModalTitle').textContent = 'Edit Barang';
    document.getElementById('fKode').value = b.kode;
    document.getElementById('fNama').value = b.nama;
    document.getElementById('fSatuan').value = b.satuan;
    document.getElementById('fHargaBeli').value = b.harga_beli;
    document.getElementById('fHargaJual').value = b.harga_jual;
    document.getElementById('fStokMinimum').value = b.stok_minimum;
    document.getElementById('fIsActive').checked = !!b.is_active;
    openModal('formModal');
}

function openDeleteById(id) {
    const b = app.items.find(i => i.id === id);
    if (!b) return;
    app.deleteTarget = b;
    document.getElementById('deleteTargetName').textContent = b.nama;
    openModal('deleteModal');
}

async function save(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        kode:          document.getElementById('fKode').value,
        nama:          document.getElementById('fNama').value,
        satuan:        document.getElementById('fSatuan').value,
        harga_beli:    parseFloat(document.getElementById('fHargaBeli').value)||0,
        harga_jual:    parseFloat(document.getElementById('fHargaJual').value)||0,
        stok_minimum:  parseFloat(document.getElementById('fStokMinimum').value)||0,
        is_active:     document.getElementById('fIsActive').checked,
    };
    const url    = app.editId ? `/api/barang/${app.editId}` : '/api/barang';
    const method = app.editId ? 'PUT' : 'POST';
    const res    = await apiFetch(url, { method, body: JSON.stringify(form) });
    const data   = await res.json();
    if (res.ok) {
        toast('Barang disimpan.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        const msg = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        toast(msg, 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function doDelete() {
    const btn = document.getElementById('btnDelete');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    const res  = await apiFetch(`/api/barang/${app.deleteTarget.id}`, { method: 'DELETE' });
    if (res.ok) {
        toast('Barang dihapus.', 'success');
        closeModal('deleteModal');
        await load(app.meta.current_page);
    } else {
        const d = await res.json();
        toast(d.message, 'error');
    }
    btn.disabled = false; btn.textContent = 'Hapus';
}

/* ── Search debounce ── */
let _searchTimer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => load(), 300);
});

document.addEventListener('DOMContentLoaded', () => load());
</script>
@endsection
