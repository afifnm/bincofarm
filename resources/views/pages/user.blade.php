@extends('layouts.app')
@section('title', 'Kelola User')

@section('content')

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Kelola User</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Manajemen akun pengguna sistem</p>
        </div>
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Search bar --}}
    <div class="mb-4">
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="search" placeholder="Cari nama / email..." class="form-input pl-9"/>
        </div>
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
                    <th>User</th>
                    <th class="hidden md:table-cell">Email</th>
                    <th class="hidden sm:table-cell text-center">Role</th>
                    <th class="hidden lg:table-cell">No. HP</th>
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
                <h3 id="formModalTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah User</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="mainForm" onsubmit="save(event)" class="space-y-4">
                    <div>
                        <label class="form-label">Nama</label>
                        <input type="text" id="fName" required class="form-input" placeholder="Nama lengkap"/>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Email <span class="text-xs font-normal" style="color:var(--color-text-muted);">(opsional)</span></label>
                            <input type="email" id="fEmail" class="form-input" placeholder="email@contoh.com"/>
                        </div>
                        <div>
                            <label class="form-label">No. HP <span class="text-xs font-normal" style="color:var(--color-text-muted);">(opsional)</span></label>
                            <input type="text" id="fPhone" class="form-input" placeholder="08xx-xxxx-xxxx"/>
                        </div>
                    </div>
                    <p class="text-xs -mt-2" style="color:var(--color-text-muted);">* Email atau No. HP harus diisi salah satu.</p>
                    <div>
                        <label class="form-label">Role</label>
                        <select id="fRole" class="form-input">
                            <option value="admin">Admin</option>
                            <option value="inventory">Inventory &amp; Kas</option>
                            <option value="pj_gh">Penanggung Jawab GH</option>
                        </select>
                    </div>
                    <div>
                        <label id="fPasswordLabel" class="form-label">Password</label>
                        <input type="password" id="fPassword" class="form-input" placeholder="Min. 6 karakter"/>
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
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus User</h3>
                <button type="button" onclick="closeModal('deleteModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="text-center py-2">
                    <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Hapus user <strong id="deleteTargetName"></strong>?</p>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);">Tindakan ini tidak dapat dibatalkan.</p>
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

function roleLabel(role) {
    return { admin: 'Admin', inventory: 'Inventory & Kas', pj_gh: 'PJ Greenhouse' }[role] || role;
}

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableContainer').classList.toggle('hidden', v);
}

function render() {
    const tbody = document.getElementById('tableBody');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <p class="text-sm font-medium">Belum ada user</p></td></tr>`;
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.items.map((user, idx) => `
        <tr class="tbl-row">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from||1)+idx}</td>
            <td class="tbl-cell">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm font-bold" style="background:var(--color-primary-soft);color:var(--color-primary);">${escHtml(user.name.charAt(0).toUpperCase())}</div>
                    <div>
                        <p class="font-semibold text-sm" style="color:var(--color-text);">${escHtml(user.name)}</p>
                        <p class="text-xs md:hidden" style="color:var(--color-text-muted);">${escHtml(user.email||'')}</p>
                    </div>
                </div>
            </td>
            <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(user.email||'')}</td>
            <td class="tbl-cell hidden sm:table-cell text-center"><span class="badge ${user.role==='admin'?'badge-info':'badge-neutral'}">${escHtml(roleLabel(user.role))}</span></td>
            <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);">${escHtml(user.phone||'—')}</td>
            <td class="tbl-cell">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openEditById(${user.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </button>
                    <button onclick="openDeleteById(${user.id})" title="Hapus" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
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
        <p class="text-xs" style="color:var(--color-text-muted);">Menampilkan ${m.from}–${m.to} dari ${m.total} user</p>
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
    let url = `/api/users?page=${page}&per_page=10`;
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
    document.getElementById('formModalTitle').textContent = 'Tambah User';
    document.getElementById('fName').value = '';
    document.getElementById('fEmail').value = '';
    document.getElementById('fPhone').value = '';
    document.getElementById('fRole').value = 'inventory';
    document.getElementById('fPassword').value = '';
    document.getElementById('fPassword').required = true;
    document.getElementById('fPasswordLabel').textContent = 'Password';
    openModal('formModal');
}

function openEditById(id) {
    const user = app.items.find(i => i.id === id);
    if (!user) return;
    app.editId = user.id;
    document.getElementById('formModalTitle').textContent = 'Edit User';
    document.getElementById('fName').value = user.name;
    document.getElementById('fEmail').value = user.email || '';
    document.getElementById('fPhone').value = user.phone || '';
    document.getElementById('fRole').value = user.role;
    document.getElementById('fPassword').value = '';
    document.getElementById('fPassword').required = false;
    document.getElementById('fPasswordLabel').textContent = 'Password Baru (kosongkan jika tidak diubah)';
    openModal('formModal');
}

function openDeleteById(id) {
    const user = app.items.find(i => i.id === id);
    if (!user) return;
    app.deleteTarget = user;
    document.getElementById('deleteTargetName').textContent = user.name;
    openModal('deleteModal');
}

async function save(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        name:  document.getElementById('fName').value,
        email: document.getElementById('fEmail').value,
        phone: document.getElementById('fPhone').value,
        role:  document.getElementById('fRole').value,
    };
    const pw = document.getElementById('fPassword').value;
    if (pw) form.password = pw;
    const url    = app.editId ? `/api/users/${app.editId}` : '/api/users';
    const method = app.editId ? 'PUT' : 'POST';
    const res    = await apiFetch(url, { method, body: JSON.stringify(form) });
    const data   = await res.json();
    if (res.ok) {
        toast(app.editId ? 'User diperbarui.' : 'User ditambahkan.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
        toast(errs || 'Gagal menyimpan.', 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
}

async function doDelete() {
    const btn = document.getElementById('btnDelete');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    try {
        const res  = await apiFetch(`/api/users/${app.deleteTarget.id}`, { method: 'DELETE' });
        if (!res) return;
        const data = await res.json();
        if (res.ok) {
            toast('User dihapus.', 'success');
            closeModal('deleteModal');
            await load(app.meta.current_page);
        } else {
            toast(data.message || 'Gagal menghapus.', 'error');
        }
    } catch (err) {
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
