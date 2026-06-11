@extends('layouts.app')
@section('title', 'Kelola User')

@section('content')
<div x-data="userApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Kelola User</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Manajemen akun pengguna sistem</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Search bar --}}
    <div class="mb-4 flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Cari nama / email..."
                   class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div x-show="!loading" x-cloak class="table-wrap">
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
            <tbody>
                <template x-for="(user, idx) in items" :key="user.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm font-bold"
                                     style="background:var(--color-primary-soft);color:var(--color-primary);"
                                     x-text="user.name.charAt(0).toUpperCase()"></div>
                                <div>
                                    <p class="font-semibold text-sm" style="color:var(--color-text);" x-text="user.name"></p>
                                    <p class="text-xs md:hidden" style="color:var(--color-text-muted);" x-text="user.email"></p>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);" x-text="user.email"></td>
                        <td class="tbl-cell hidden sm:table-cell text-center">
                            <span class="badge" :class="user.role==='admin' ? 'badge-info' : 'badge-neutral'" x-text="user.role"></span>
                        </td>
                        <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);" x-text="user.phone || '—'"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openEdit(user)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </button>
                                <button @click="openDelete(user)" title="Hapus"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && items.length===0">
                    <td colspan="6" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <p class="text-sm font-medium">Belum ada user</p>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                Menampilkan <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span> user
            </p>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageRange" :key="p">
                    <button @click="goPage(p)" :disabled="p === '...'"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs"
                            :style="p === meta.current_page ? 'background:var(--color-primary);color:#fff;' : 'border:1px solid var(--color-border);'"
                            x-text="p"></button>
                </template>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId ? 'Edit User' : 'Tambah User'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Nama</label>
                        <input type="text" x-model="form.name" required class="form-input" placeholder="Nama lengkap"/>
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" x-model="form.email" required class="form-input" placeholder="email@contoh.com"/>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Role</label>
                        <select x-model="form.role" class="form-input">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. HP</label>
                        <input type="text" x-model="form.phone" class="form-input" placeholder="08xx-xxxx-xxxx"/>
                    </div>
                </div>
                <div>
                    <label class="form-label" x-text="editId ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password'"></label>
                    <input type="password" x-model="form.password" :required="!editId" class="form-input" placeholder="Min. 6 karakter"/>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving" x-cloak class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Hapus --}}
    <div x-show="confirmDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="confirmDelete=false">
        <div class="absolute inset-0"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);"
             @click="confirmDelete=false"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl"
             style="background:var(--color-surface);border:1px solid var(--color-border);"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus User</h3>
                <button @click="confirmDelete=false"
                        class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors"
                        style="color:var(--color-text-muted);"
                        onmouseover="this.style.background='var(--color-bg)'"
                        onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="text-center py-2">
                    <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-text);">
                        Hapus user <strong x-text="deleteTarget?.name"></strong>?
                    </p>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="confirmDelete=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button @click="doDelete()" :disabled="deleting" class="btn btn-danger flex-1 justify-center">
                        <template x-if="!deleting"><span>Hapus</span></template>
                        <template x-if="deleting"><span>Menghapus...</span></template>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function userApp() {
    return {
        items: [], loading: true, open: false, saving: false,
        confirmDelete: false, deleteTarget: null, editId: null, deleting: false,
        search: '',
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form: { name:'', email:'', role:'user', phone:'', password:'' },

        get pageRange() {
            const cur = this.meta.current_page, last = this.meta.last_page;
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) { for(let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
            else if (cur >= last - 3) { pages.push(1); pages.push('...'); for(let i=last-4;i<=last;i++) pages.push(i); }
            else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
            return pages;
        },

        async load(page = 1) {
            this.loading = true;
            let url = `/api/users?page=${page}&per_page=10`;
            if (this.search) url += `&search=${encodeURIComponent(this.search)}`;
            const res = await apiFetch(url);
            if (res?.ok) {
                const d = await res.json();
                this.items = d.data || [];
                if (d.meta) this.meta = d.meta;
                else this.meta = { current_page: d.current_page, last_page: d.last_page, from: d.from, to: d.to, total: d.total };
            }
            this.loading = false;
        },

        goPage(p) { if (p !== '...' && p >= 1 && p <= this.meta.last_page) this.load(p); },

        openCreate() {
            this.editId = null;
            this.form = { name:'', email:'', role:'user', phone:'', password:'' };
            this.open = true;
        },

        openEdit(user) {
            this.editId = user.id;
            this.form = { name: user.name, email: user.email, role: user.role, phone: user.phone || '', password: '' };
            this.open = true;
        },

        openDelete(user) {
            this.deleteTarget = user;
            this.deleting = false;
            this.confirmDelete = true;
        },

        async save() {
            this.saving = true;
            const url    = this.editId ? `/api/users/${this.editId}` : '/api/users';
            const method = this.editId ? 'PUT' : 'POST';
            const res    = await apiFetch(url, { method, body: JSON.stringify(this.form) });
            const data   = await res.json();
            if (res.ok) {
                toast(this.editId ? 'User diperbarui.' : 'User ditambahkan.', 'success');
                this.open = false;
                await this.load(this.meta.current_page);
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                toast(errs || 'Gagal menyimpan.', 'error');
            }
            this.saving = false;
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res  = await apiFetch(`/api/users/${this.deleteTarget.id}`, { method: 'DELETE' });
                if (!res) return;
                const data = await res.json();
                if (res.ok) {
                    toast('User dihapus.', 'success');
                    this.confirmDelete = false;
                    await this.load(this.meta.current_page);
                } else {
                    toast(data.message || 'Gagal menghapus.', 'error');
                }
            } catch (e) {
                toast('Terjadi kesalahan.', 'error');
            } finally {
                this.deleting = false;
            }
        },
    }
}
</script>
@endsection
