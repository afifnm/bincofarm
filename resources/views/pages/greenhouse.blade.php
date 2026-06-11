@extends('layouts.app')
@section('title', 'Greenhouse — Master')

@section('content')
<div x-data="greenhouseApp()" x-init="load()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs mb-4" style="color:var(--color-text-muted);">
        <span>Greenhouse</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-primary);">Master Greenhouse</span>
    </nav>

    {{-- Sub nav tabs --}}
    <div class="flex gap-1.5 mb-5">
        <a href="{{ route('greenhouse') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-primary);color:#fff;">
            Greenhouse
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('jenis-melon') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);"
           onmouseover="this.style.background='var(--color-bg)'"
           onmouseout="this.style.background='var(--color-surface)'">
            Jenis Melon
        </a>
        @endif
    </div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Master Greenhouse</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola data greenhouse dan penanggung jawab</p>
        </div>
        @if(auth()->user()->isAdmin())
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah GH
        </button>
        @endif
    </div>

    {{-- Search --}}
    <div class="mb-4 flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Cari greenhouse..."
                   class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div x-show="!loading" x-cloak class="table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Greenhouse</th>
                    <th class="hidden md:table-cell">Penanggung Jawab</th>
                    <th class="hidden lg:table-cell">Kas Terhubung</th>
                    <th class="text-center hidden md:table-cell">Pohon Hidup</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(gh, idx) in items" :key="gh.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell">
                            <div>
                                <p class="font-semibold text-sm" style="color:var(--color-text);" x-text="gh.nama"></p>
                                <p class="text-xs" style="color:var(--color-text-muted);" x-text="gh.lokasi || '—'"></p>
                            </div>
                        </td>
                        <td class="tbl-cell hidden md:table-cell text-sm" x-text="gh.user?.name || '—'"></td>
                        <td class="tbl-cell hidden lg:table-cell text-sm" x-text="gh.kas?.nama || '—'"></td>
                        <td class="tbl-cell text-center hidden md:table-cell">
                            <span class="text-sm font-medium" style="color:var(--color-primary);"
                                  x-text="gh.populasi ? gh.populasi.pohon_hidup : '—'"></span>
                        </td>
                        <td class="tbl-cell text-center">
                            <span class="badge" :class="gh.is_active ? 'badge-success' : 'badge-neutral'"
                                  x-text="gh.is_active ? 'Aktif' : 'Nonaktif'"></span>
                        </td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openPopulasi(gh)" title="Update Populasi"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='#059669'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                                </button>
                                @if(auth()->user()->isAdmin())
                                <button @click="openEdit(gh)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                                <button @click="openDelete(gh)" title="Hapus"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && items.length===0">
                    <td colspan="7" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        <p class="text-sm font-medium">Belum ada greenhouse</p>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                Menampilkan <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span> data
            </p>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageRange" :key="p">
                    <button @click="goPage(p)" :disabled="p === '...'"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors"
                            :style="p === meta.current_page ? 'background:var(--color-primary);color:#fff;' : 'border:1px solid var(--color-border);'"
                            x-text="p"></button>
                </template>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Form GH --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <template x-if="open">
                <div>
                    <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId ? 'Edit Greenhouse' : 'Tambah Greenhouse'"></h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="form-label">Nama Greenhouse</label>
                            <input type="text" x-model="form.nama" required class="form-input" placeholder="Nama GH"/>
                        </div>
                        <div>
                            <label class="form-label">Lokasi</label>
                            <input type="text" x-model="form.lokasi" class="form-input" placeholder="Lokasi (opsional)"/>
                        </div>
                        <div>
                            <label class="form-label">Penanggung Jawab</label>
                            <select x-model="form.user_id" class="form-input">
                                <option value="">— Pilih User —</option>
                                <template x-for="u in users" :key="u.id">
                                    <option :value="u.id" x-text="u.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Rekening Kas</label>
                            <select x-model="form.kas_id" required class="form-input">
                                <option value="">— Pilih Kas —</option>
                                <template x-for="k in kasList" :key="k.id">
                                    <option :value="k.id" x-text="k.nama"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 py-1">
                            <input type="checkbox" x-model="form.is_active" id="gh_active"
                                   class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                            <label for="gh_active" class="text-sm cursor-pointer" style="color:var(--color-text);">Greenhouse aktif</label>
                        </div>
                        <div class="flex gap-3 pt-2">
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
                </div>
            </template>
        </x-modal>
    </div>

    {{-- Modal Populasi --}}
    <div x-show="openPop" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <template x-if="openPop">
                <div>
                    <h3 class="text-base font-semibold mb-1" style="color:var(--color-text);">Update Populasi Pohon</h3>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);" x-text="'GH: ' + (popTarget?.nama || '')"></p>
                    <form @submit.prevent="savePop" class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="form-label">Total Pohon</label>
                                <input type="number" x-model="popForm.total_pohon" min="0" required class="form-input"/>
                            </div>
                            <div>
                                <label class="form-label">Pohon Hidup</label>
                                <input type="number" x-model="popForm.pohon_hidup" min="0" required class="form-input"/>
                            </div>
                            <div>
                                <label class="form-label">Pohon Mati</label>
                                <input type="number" x-model="popForm.pohon_mati" min="0" required class="form-input"/>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Catatan</label>
                            <input type="text" x-model="popForm.catatan" class="form-input" placeholder="Opsional"/>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="openPop=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                            <button type="submit" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                                <span x-show="!saving">Simpan</span>
                                <span x-show="saving" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </x-modal>
    </div>

    {{-- Modal Hapus --}}
    <div x-show="confirmDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @keydown.escape.window="confirmDelete=false">
        <div class="absolute inset-0" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);" @click="confirmDelete=false"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl" style="background:var(--color-surface);border:1px solid var(--color-border);" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus Greenhouse</h3>
                <button @click="confirmDelete=false" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="text-center py-2">
                    <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Hapus <strong x-text="deleteTarget?.nama"></strong>?</p>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);">Data greenhouse akan diarsipkan.</p>
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
function greenhouseApp() {
    return {
        items: [], loading: true, open: false, saving: false,
        confirmDelete: false, deleteTarget: null, editId: null, deleting: false,
        search: '', users: [], kasList: [],
        openPop: false, popTarget: null,
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form: { nama:'', lokasi:'', user_id:'', kas_id:'', is_active:true },
        popForm: { total_pohon:0, pohon_hidup:0, pohon_mati:0, catatan:'' },

        get pageRange() {
            const cur = this.meta.current_page, last = this.meta.last_page;
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) { for(let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
            else if (cur >= last - 3) { pages.push(1); pages.push('...'); for(let i=last-4;i<=last;i++) pages.push(i); }
            else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
            return pages;
        },

        async init() {
            // Dropdown user & kas hanya dipakai modal tambah/edit (admin)
            if (!@json(auth()->user()->isAdmin())) return;
            const [usersRes, kasRes] = await Promise.all([
                apiFetch('/api/users'),
                apiFetch('/api/kas?semua=0&per_page=100'),
            ]);
            if (usersRes?.ok) { const d = await usersRes.json(); this.users = d.data || []; }
            if (kasRes?.ok)   { const d = await kasRes.json();   this.kasList = d.data || []; }
        },

        async load(page = 1) {
            this.loading = true;
            let url = `/api/greenhouse?page=${page}&per_page=15`;
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
            this.form = { nama:'', lokasi:'', user_id:'', kas_id:'', is_active:true };
            this.open = true;
        },

        openEdit(gh) {
            this.editId = gh.id;
            this.form = { nama:gh.nama, lokasi:gh.lokasi||'', user_id:gh.user_id||'', kas_id:gh.kas_id, is_active:gh.is_active };
            this.open = true;
        },

        openDelete(gh) { this.deleteTarget = gh; this.deleting = false; this.confirmDelete = true; },

        openPopulasi(gh) {
            this.popTarget = gh;
            this.popForm = {
                total_pohon: gh.populasi?.total_pohon ?? 0,
                pohon_hidup: gh.populasi?.pohon_hidup ?? 0,
                pohon_mati:  gh.populasi?.pohon_mati  ?? 0,
                catatan: '',
            };
            this.openPop = true;
        },

        async save() {
            this.saving = true;
            const url    = this.editId ? `/api/greenhouse/${this.editId}` : '/api/greenhouse';
            const method = this.editId ? 'PUT' : 'POST';
            const payload = { ...this.form };
            if (!payload.user_id) payload.user_id = null;
            const res  = await apiFetch(url, { method, body: JSON.stringify(payload) });
            const data = await res.json();
            if (res.ok) {
                toast(this.editId ? 'Greenhouse diperbarui.' : 'Greenhouse ditambah.', 'success');
                this.open = false;
                await this.load(this.meta.current_page);
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                toast(errs, 'error');
            }
            this.saving = false;
        },

        async savePop() {
            this.saving = true;
            const res  = await apiFetch(`/api/greenhouse/${this.popTarget.id}/populasi`, {
                method: 'PUT',
                body: JSON.stringify(this.popForm),
            });
            const data = await res.json();
            if (res.ok) {
                toast('Populasi diperbarui.', 'success');
                this.openPop = false;
                await this.load(this.meta.current_page);
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                toast(errs, 'error');
            }
            this.saving = false;
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res = await apiFetch(`/api/greenhouse/${this.deleteTarget.id}`, { method: 'DELETE' });
                if (!res) return;
                const data = await res.json();
                if (res.ok) {
                    toast('Greenhouse dihapus.', 'success');
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
