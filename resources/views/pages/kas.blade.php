@extends('layouts.app')
@section('title', 'Master Kas — Rekening')

@section('breadcrumb')
    <span>Master Kas</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Rekening</span>
@endsection

@section('content')

    {{-- Sub nav tabs --}}
    <div class="flex gap-1.5 mb-5">
        <a href="{{ route('kas') }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors" style="background:var(--color-primary);color:#fff;">Rekening</a>
        <a href="{{ route('kategori') }}" class="px-4 py-2 rounded-xl text-sm font-medium transition-colors" style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background='var(--color-surface)'">Kategori</a>
    </div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Rekening Kas</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola akun kas, bank, dan e-wallet</p>
        </div>
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Rekening
        </button>
    </div>

    {{-- Search bar --}}
    <div class="mb-4">
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="search" placeholder="Cari rekening..." class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div id="tableContainer" class="hidden table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Rekening</th>
                    <th class="hidden md:table-cell">Tipe</th>
                    <th class="text-right">Saldo</th>
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
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah Rekening</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="save(event)" class="space-y-4">
                    <div>
                        <label class="form-label">Tipe</label>
                        <select id="fTipe" class="form-input">
                            <option value="tunai">Tunai</option>
                            <option value="bank">Bank</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Nama Rekening</label>
                        <input type="text" id="fNama" required class="form-input" placeholder="Nama akun kas"/>
                    </div>
                    <div id="saldoAwalRow">
                        <label class="form-label">Saldo Awal</label>
                        <input type="number" id="fSaldoAwal" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="fIsActive" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                        <label for="fIsActive" class="text-sm cursor-pointer" style="color:var(--color-text);">Rekening aktif</label>
                    </div>
                    <div class="flex gap-3 pt-2">
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
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus Rekening</h3>
                <button type="button" onclick="closeModal('deleteModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="text-center py-2">
                    <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Hapus rekening <strong id="deleteTargetName"></strong>?</p>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);">Rekening akan dipindahkan ke arsip dan tidak muncul di daftar aktif.</p>
                </div>
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
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatRp(n) { return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }

function kasIconHtml(tipe) {
    if (tipe === 'tunai') return `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>`;
    if (tipe === 'bank') return `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>`;
    return `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/></svg>`;
}

function kasIconClass(tipe) {
    return tipe === 'tunai' ? 'icon-wrap-emerald' : tipe === 'bank' ? 'icon-wrap-blue' : 'icon-wrap-purple';
}

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableContainer').classList.toggle('hidden', v);
}

function render() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);"><p class="text-sm font-medium">Belum ada rekening</p></td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.items.map((kas, idx) => `
        <tr class="tbl-row">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 ${kasIconClass(kas.tipe)}">${kasIconHtml(kas.tipe)}</div>
                    <p class="font-semibold text-sm" style="color:var(--color-text);">${escHtml(kas.nama)}</p>
                </div>
            </td>
            <td class="tbl-cell hidden md:table-cell"><span class="badge badge-neutral">${escHtml(kas.tipe_label||kas.tipe)}</span></td>
            <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);">${formatRp(kas.saldo_berjalan)}</td>
            <td class="tbl-cell text-center"><span class="badge ${kas.is_active?'badge-success':'badge-neutral'}">${kas.is_active?'Aktif':'Nonaktif'}</span></td>
            <td class="tbl-cell">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openEditById(${kas.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </button>
                    <button onclick="openDeleteById(${kas.id})" title="Hapus" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
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
        <p class="text-xs" style="color:var(--color-text-muted);">Menampilkan ${m.from}–${m.to} dari ${m.total} rekening</p>
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
    let url = `/api/kas?page=${page}&per_page=10`;
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

function goPage(p) { if (p !== '...' && p >= 1 && p <= app.meta.last_page) load(p); }

function openCreate() {
    app.editId = null;
    document.getElementById('formModalTitle').textContent = 'Tambah Rekening';
    document.getElementById('fTipe').value = 'tunai';
    document.getElementById('fNama').value = '';
    document.getElementById('fSaldoAwal').value = 0;
    document.getElementById('fIsActive').checked = true;
    document.getElementById('saldoAwalRow').classList.remove('hidden');
    openModal('formModal');
}

function openEditById(id) {
    const kas = app.items.find(i => i.id === id);
    if (!kas) return;
    app.editId = kas.id;
    document.getElementById('formModalTitle').textContent = 'Edit Rekening';
    document.getElementById('fTipe').value = kas.tipe;
    document.getElementById('fNama').value = kas.nama;
    document.getElementById('fSaldoAwal').value = kas.saldo_awal || 0;
    document.getElementById('fIsActive').checked = !!kas.is_active;
    document.getElementById('saldoAwalRow').classList.add('hidden');
    openModal('formModal');
}

function openDeleteById(id) {
    const kas = app.items.find(i => i.id === id);
    if (!kas) return;
    app.deleteTarget = kas;
    document.getElementById('deleteTargetName').textContent = kas.nama;
    openModal('deleteModal');
}

async function save(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        tipe:      document.getElementById('fTipe').value,
        nama:      document.getElementById('fNama').value,
        is_active: document.getElementById('fIsActive').checked,
    };
    if (!app.editId) form.saldo_awal = parseFloat(document.getElementById('fSaldoAwal').value) || 0;
    const url    = app.editId ? `/api/kas/${app.editId}` : '/api/kas';
    const method = app.editId ? 'PUT' : 'POST';
    const res    = await apiFetch(url, { method, body: JSON.stringify(form) });
    const data   = await res.json();
    if (res.ok) {
        toast(app.editId ? 'Rekening diperbarui.' : 'Rekening ditambah.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        toast(errs, 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function doDelete() {
    const btn = document.getElementById('btnDelete');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    try {
        const res  = await apiFetch(`/api/kas/${app.deleteTarget.id}`, { method: 'DELETE' });
        if (!res) return;
        const data = await res.json();
        if (res.ok) {
            toast('Rekening dihapus.', 'success');
            closeModal('deleteModal');
            await load(app.meta.current_page);
        } else {
            toast(data.message || 'Gagal menghapus.', 'error');
        }
    } catch (e) {
        toast('Terjadi kesalahan.', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Hapus';
    }
}

let _searchTimer;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => load(), 300);
});

document.addEventListener('DOMContentLoaded', () => load());
</script>
@endsection
