@extends('layouts.app')
@section('title', 'Master Kas — Kategori')

@section('breadcrumb')
    <span>Master Kas</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Kategori</span>
@endsection

@section('content')

    {{-- Sub nav tabs --}}
    <div class="flex gap-1.5 mb-5">
        <a href="{{ route('kas') }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors" style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background='var(--color-surface)'">Rekening</a>
        <a href="{{ route('kategori') }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors" style="background:var(--color-primary);color:#fff;">Kategori</a>
    </div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Kategori Transaksi</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola kategori pemasukan dan pengeluaran</p>
        </div>
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- Filter + Search --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <div class="relative flex-1 min-w-48 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="search" placeholder="Cari kategori..." class="form-input pl-9"/>
        </div>
        <button id="filterAll" onclick="setFilter('')" class="filter-pill active">Semua</button>
        <button id="filterMasuk" onclick="setFilter('masuk')" class="filter-pill">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
            Masuk
        </button>
        <button id="filterKeluar" onclick="setFilter('keluar')" class="filter-pill">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
            Keluar
        </button>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div id="tableContainer" class="hidden table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Nama Kategori</th>
                    <th class="text-center">Jenis</th>
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
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah Kategori</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="save(event)" class="space-y-4">
                    <div>
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" id="fNama" required class="form-input" placeholder="cth. Pembelian BBM"/>
                    </div>
                    <div>
                        <label class="form-label">Jenis</label>
                        <select id="fJenis" class="form-input">
                            <option value="masuk">Masuk (Pemasukan)</option>
                            <option value="keluar">Keluar (Pengeluaran)</option>
                        </select>
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
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus Kategori</h3>
                <button type="button" onclick="closeModal('deleteModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm mb-5" style="color:var(--color-text);">
                    Hapus kategori <strong id="deleteTargetName"></strong>? Pastikan tidak ada transaksi yang menggunakan kategori ini.
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
    items: [], loading: true, editId: null, deleteTarget: null,
    filterJenis: '',
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function setFilter(jenis) {
    app.filterJenis = jenis;
    document.getElementById('filterAll').className = 'filter-pill' + (jenis===''?' active':'');
    document.getElementById('filterMasuk').className = 'filter-pill' + (jenis==='masuk'?' active-success':'');
    document.getElementById('filterKeluar').className = 'filter-pill' + (jenis==='keluar'?' active-danger':'');
    load();
}

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableContainer').classList.toggle('hidden', v);
}

function render() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="4" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">Tidak ada kategori.</td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    const arrowUp = `<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>`;
    const arrowDn = `<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>`;
    tbody.innerHTML = app.items.map((item, idx) => `
        <tr class="tbl-row">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell font-medium" style="color:var(--color-text);">${escHtml(item.nama)}</td>
            <td class="tbl-cell text-center">
                <span class="badge ${item.jenis==='masuk'?'badge-success':'badge-danger'}">
                    ${item.jenis==='masuk'?arrowUp:arrowDn}
                    ${escHtml(item.jenis_label||item.jenis)}
                </span>
            </td>
            <td class="tbl-cell">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openEditById(${item.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </button>
                    <button onclick="${item.in_use?'':('openDeleteById('+item.id+')')}" title="${item.in_use?'Kategori sudah digunakan di transaksi':'Hapus'}" ${item.in_use?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="${item.in_use?'color:var(--color-border);cursor:not-allowed;':'color:var(--color-text-muted);'}" ${item.in_use?'':' onmouseover="this.style.background=\'#FEE2E2\';this.style.color=\'#B91C1C\'" onmouseout="this.style.background=\'\';this.style.color=\'var(--color-text-muted)\'"'}>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
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
    let url = `/api/kategori-transaksi?page=${page}&per_page=15`;
    const s = document.getElementById('search').value;
    if (s) url += `&search=${encodeURIComponent(s)}`;
    if (app.filterJenis) url += `&jenis=${app.filterJenis}`;
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

function openCreate() {
    app.editId = null;
    document.getElementById('formModalTitle').textContent = 'Tambah Kategori';
    document.getElementById('fNama').value = '';
    document.getElementById('fJenis').value = 'masuk';
    openModal('formModal');
}

function openEditById(id) {
    const item = app.items.find(i => i.id === id);
    if (!item) return;
    app.editId = item.id;
    document.getElementById('formModalTitle').textContent = 'Edit Kategori';
    document.getElementById('fNama').value = item.nama;
    document.getElementById('fJenis').value = item.jenis;
    openModal('formModal');
}

function openDeleteById(id) {
    const item = app.items.find(i => i.id === id);
    if (!item) return;
    app.deleteTarget = item;
    document.getElementById('deleteTargetName').textContent = item.nama;
    openModal('deleteModal');
}

async function save(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        nama:  document.getElementById('fNama').value,
        jenis: document.getElementById('fJenis').value,
    };
    const url    = app.editId ? `/api/kategori-transaksi/${app.editId}` : '/api/kategori-transaksi';
    const method = app.editId ? 'PUT' : 'POST';
    const res    = await apiFetch(url, { method, body: JSON.stringify(form) });
    const data   = await res.json();
    if (res.ok) {
        toast('Kategori disimpan.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function doDelete() {
    const btn = document.getElementById('btnDelete');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    const res  = await apiFetch(`/api/kategori-transaksi/${app.deleteTarget.id}`, { method: 'DELETE' });
    if (res.ok) {
        toast('Kategori dihapus.', 'success');
        closeModal('deleteModal');
        await load(app.meta.current_page);
    } else {
        const d = await res.json();
        toast(d.message, 'error');
    }
    btn.disabled = false; btn.textContent = 'Hapus';
}

let _searchTimer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => load(), 300);
});

document.addEventListener('DOMContentLoaded', () => load());
</script>
@endsection
