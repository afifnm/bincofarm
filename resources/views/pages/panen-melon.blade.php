@extends('layouts.app')
@section('title', 'Greenhouse — Panen Melon')

@section('content')
<div x-data="panenMelonApp()" x-init="init()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs mb-4" style="color:var(--color-text-muted);">
        <span>Greenhouse</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-primary);">Panen Melon</span>
    </nav>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Panen Melon</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Catat hasil panen per greenhouse</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Catat Panen
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <select x-model="filter.greenhouse_id" @change="load()" class="form-input w-auto min-w-[160px]">
            <option value="">Semua GH</option>
            <template x-for="gh in greenhouses" :key="gh.id">
                <option :value="gh.id" x-text="gh.nama"></option>
            </template>
        </select>
        <select x-model="filter.grade" @change="load()" class="form-input w-auto">
            <option value="">Semua Grade</option>
            <template x-for="g in ['A','B','C','D','E']" :key="g">
                <option :value="g" x-text="'Grade ' + g"></option>
            </template>
        </select>
        <input type="date" x-model="filter.dari" @change="load()" class="form-input w-auto" placeholder="Dari"/>
        <input type="date" x-model="filter.sampai" @change="load()" class="form-input w-auto" placeholder="Sampai"/>
        <button @click="filter={greenhouse_id:'',grade:'',dari:'',sampai:''};load()" class="btn btn-secondary text-xs">Reset</button>
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
                    <th>Tanggal</th>
                    <th>Greenhouse</th>
                    <th class="hidden md:table-cell">Jenis Melon</th>
                    <th class="text-right">Berat (kg)</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center hidden md:table-cell">Busuk</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(p, idx) in items" :key="p.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);" x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell text-sm" x-text="formatDate(p.tanggal)"></td>
                        <td class="tbl-cell text-sm font-medium" x-text="p.greenhouse?.nama || '—'"></td>
                        <td class="tbl-cell text-sm hidden md:table-cell" x-text="p.jenis_melon?.nama || '—'"></td>
                        <td class="tbl-cell text-right font-semibold" style="color:var(--color-primary);" x-text="Number(p.berat).toLocaleString('id-ID', {minimumFractionDigits:2})"></td>
                        <td class="tbl-cell text-center">
                            <span class="badge" :class="{'badge-success':p.grade==='A','badge-neutral':p.grade!=='A'}" x-text="p.grade_label"></span>
                        </td>
                        <td class="tbl-cell text-center hidden md:table-cell">
                            <span x-show="p.is_busuk" class="badge badge-rose">Busuk</span>
                            <span x-show="!p.is_busuk" class="text-xs" style="color:var(--color-text-muted);">—</span>
                        </td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openEdit(p)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                                <button @click="openDelete(p)" title="Hapus"
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
                    <td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <p class="text-sm font-medium">Belum ada data panen</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                Menampilkan <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span>
            </p>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
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
            <template x-if="open">
                <div>
                    <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId ? 'Edit Panen' : 'Catat Panen'"></h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="form-label">Greenhouse</label>
                            <select x-model="form.greenhouse_id" required class="form-input">
                                <option value="">— Pilih GH —</option>
                                <template x-for="gh in greenhouses" :key="gh.id">
                                    <option :value="gh.id" x-text="gh.nama"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jenis Melon</label>
                            <select x-model="form.jenis_melon_id" required class="form-input">
                                <option value="">— Pilih Jenis —</option>
                                <template x-for="jm in jenisList" :key="jm.id">
                                    <option :value="jm.id" x-text="jm.nama"></option>
                                </template>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Berat (kg)</label>
                                <input type="number" x-model="form.berat" min="0.01" step="0.01" required class="form-input"/>
                            </div>
                            <div>
                                <label class="form-label">Grade</label>
                                <select x-model="form.grade" required class="form-input">
                                    <option value="">— Grade —</option>
                                    <template x-for="g in ['A','B','C','D','E']" :key="g">
                                        <option :value="g" x-text="'Grade ' + g"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Tanggal Panen</label>
                            <input type="date" x-model="form.tanggal" required class="form-input"/>
                        </div>
                        <div class="flex items-center gap-2 py-1">
                            <input type="checkbox" x-model="form.is_busuk" id="panen_busuk"
                                   class="w-4 h-4 rounded cursor-pointer" style="accent-color:#EF4444"/>
                            <label for="panen_busuk" class="text-sm cursor-pointer" style="color:var(--color-text);">Buah busuk/afkir</label>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
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
         @keydown.escape.window="confirmDelete=false">
        <div class="absolute inset-0" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);" @click="confirmDelete=false"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl" style="background:var(--color-surface);border:1px solid var(--color-border);" @click.stop>
            <div class="px-5 py-5">
                <p class="text-sm font-medium mb-1 text-center" style="color:var(--color-text);">Hapus data panen ini?</p>
                <p class="text-xs mb-5 text-center" style="color:var(--color-text-muted);">Data akan diarsipkan.</p>
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
function panenMelonApp() {
    return {
        items: [], loading: true, open: false, saving: false,
        confirmDelete: false, deleteTarget: null, editId: null, deleting: false,
        greenhouses: [], jenisList: [],
        filter: { greenhouse_id:'', grade:'', dari:'', sampai:'' },
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form: { greenhouse_id:'', jenis_melon_id:'', berat:'', grade:'', is_busuk:false, tanggal:'' },

        async init() {
            const today = new Date().toISOString().split('T')[0];
            this.form.tanggal = today;
            const [ghRes, jmRes] = await Promise.all([
                apiFetch('/api/greenhouse?semua=1'),
                apiFetch('/api/jenis-melon?semua=1'),
            ]);
            if (ghRes?.ok) { const d = await ghRes.json(); this.greenhouses = d.data || []; }
            if (jmRes?.ok) { const d = await jmRes.json(); this.jenisList = d.data || []; }
            await this.load();
        },

        async load(page = 1) {
            this.loading = true;
            let url = `/api/panen-melon?page=${page}&per_page=20`;
            if (this.filter.greenhouse_id) url += `&greenhouse_id=${this.filter.greenhouse_id}`;
            if (this.filter.grade) url += `&grade=${this.filter.grade}`;
            if (this.filter.dari) url += `&dari=${this.filter.dari}`;
            if (this.filter.sampai) url += `&sampai=${this.filter.sampai}`;
            const res = await apiFetch(url);
            if (res?.ok) {
                const d = await res.json();
                this.items = d.data || [];
                if (d.meta) this.meta = d.meta;
                else this.meta = { current_page: d.current_page, last_page: d.last_page, from: d.from, to: d.to, total: d.total };
            }
            this.loading = false;
        },

        goPage(p) { if (p >= 1 && p <= this.meta.last_page) this.load(p); },

        openCreate() {
            this.editId = null;
            this.form = { greenhouse_id:'', jenis_melon_id:'', berat:'', grade:'', is_busuk:false, tanggal: new Date().toISOString().split('T')[0] };
            this.open = true;
        },

        openEdit(p) {
            this.editId = p.id;
            this.form = { greenhouse_id:p.greenhouse_id, jenis_melon_id:p.jenis_melon_id, berat:p.berat, grade:p.grade, is_busuk:p.is_busuk, tanggal:p.tanggal };
            this.open = true;
        },

        openDelete(p) { this.deleteTarget = p; this.deleting = false; this.confirmDelete = true; },

        async save() {
            this.saving = true;
            const url    = this.editId ? `/api/panen-melon/${this.editId}` : '/api/panen-melon';
            const method = this.editId ? 'PUT' : 'POST';
            const res    = await apiFetch(url, { method, body: JSON.stringify(this.form) });
            const data   = await res.json();
            if (res.ok) {
                toast(this.editId ? 'Panen diperbarui.' : 'Panen dicatat.', 'success');
                this.open = false;
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
                const res = await apiFetch(`/api/panen-melon/${this.deleteTarget.id}`, { method: 'DELETE' });
                if (!res) return;
                const data = await res.json();
                if (res.ok) {
                    toast('Data panen dihapus.', 'success');
                    this.confirmDelete = false;
                    await this.load(this.meta.current_page);
                } else {
                    toast(data.message || 'Gagal menghapus.', 'error');
                }
            } finally {
                this.deleting = false;
            }
        },
    }
}
</script>
@endsection
