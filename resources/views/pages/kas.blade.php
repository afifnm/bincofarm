@extends('layouts.app')
@section('title', 'Master Kas')

@section('content')
<div x-data="kasApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Master Kas</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola akun kas, bank, dan e-wallet</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Kas
        </button>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-3">
        @for($i=0;$i<3;$i++)
        <div class="skeleton rounded-2xl h-20"></div>
        @endfor
    </div>

    {{-- Kas list --}}
    <div x-show="!loading" x-cloak class="space-y-3">
        <template x-for="kas in items" :key="kas.id">
            <div class="card card-p flex items-center justify-between gap-4 transition-shadow hover:shadow-md">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="icon-wrap shrink-0"
                         :class="kas.tipe==='tunai' ? 'icon-wrap-emerald' : kas.tipe==='bank' ? 'icon-wrap-blue' : 'icon-wrap-purple'">
                        <svg x-show="kas.tipe==='tunai'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                        <svg x-show="kas.tipe==='bank'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                        </svg>
                        <svg x-show="kas.tipe==='ewallet'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:var(--color-text);" x-text="kas.nama"></p>
                        <p class="text-xs mt-0.5" style="color:var(--color-text-muted);" x-text="kas.kode + ' · ' + kas.tipe_label"></p>
                    </div>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-right">
                        <p class="text-sm font-bold" style="color:var(--color-primary);" x-text="formatRp(kas.saldo_berjalan)"></p>
                        <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Saldo</p>
                    </div>
                    <div class="flex gap-1.5">
                        <button @click="openEdit(kas)" class="btn btn-sm btn-secondary" title="Edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                            </svg>
                            Edit
                        </button>
                        <button @click="openDelete(kas)" class="btn btn-sm btn-danger" title="Hapus">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <div x-show="items.length === 0" class="text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
            <p class="text-sm font-medium">Belum ada kas</p>
            <p class="text-xs mt-1">Tambah kas pertama untuk memulai</p>
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="">
            <template x-if="open">
                <div>
                    <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId ? 'Edit Kas' : 'Tambah Kas'"></h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Kode</label>
                                <input type="text" x-model="form.kode" required class="form-input" placeholder="KAS-01"/>
                            </div>
                            <div>
                                <label class="form-label">Tipe</label>
                                <select x-model="form.tipe" class="form-input">
                                    <option value="tunai">Tunai</option>
                                    <option value="bank">Bank</option>
                                    <option value="ewallet">E-Wallet</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Nama Kas</label>
                            <input type="text" x-model="form.nama" required class="form-input" placeholder="Nama akun kas"/>
                        </div>
                        <div x-show="!editId">
                            <label class="form-label">Saldo Awal</label>
                            <input type="number" x-model="form.saldo_awal" min="0" step="0.01" class="form-input" placeholder="0"/>
                        </div>
                        <div class="flex items-center gap-2 py-1">
                            <input type="checkbox" x-model="form.is_active" id="is_active_kas"
                                   class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                            <label for="is_active_kas" class="text-sm cursor-pointer" style="color:var(--color-text);">Kas aktif</label>
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

    {{-- Modal Hapus --}}
    <div x-show="confirmDelete" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Hapus Kas">
            <div class="text-center py-2">
                <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                </div>
                <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Hapus kas <strong x-text="deleteTarget?.nama"></strong>?</p>
                <p class="text-xs mb-5" style="color:var(--color-text-muted);">Aksi ini tidak dapat dibatalkan.</p>
            </div>
            <div class="flex gap-3">
                <button @click="confirmDelete=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
            </div>
        </x-modal>
    </div>

</div>

<script>
function kasApp() {
    return {
        items: [], loading: true, open: false, saving: false,
        confirmDelete: false, deleteTarget: null, editId: null,
        form: { kode:'', nama:'', tipe:'tunai', saldo_awal:0, is_active:true },

        async load() {
            this.loading = true;
            const res = await apiFetch('/api/kas?paginate=100');
            if (res?.ok) { const d = await res.json(); this.items = d.data || []; }
            this.loading = false;
        },

        openCreate() {
            this.editId = null;
            this.form = { kode:'', nama:'', tipe:'tunai', saldo_awal:0, is_active:true };
            this.open = true;
        },

        openEdit(kas) {
            this.editId = kas.id;
            this.form = { kode:kas.kode, nama:kas.nama, tipe:kas.tipe, saldo_awal:kas.saldo_awal, is_active:kas.is_active };
            this.open = true;
        },

        openDelete(kas) { this.deleteTarget = kas; this.confirmDelete = true; },

        async save() {
            this.saving = true;
            const url    = this.editId ? `/api/kas/${this.editId}` : '/api/kas';
            const method = this.editId ? 'PUT' : 'POST';
            const res    = await apiFetch(url, { method, body: JSON.stringify(this.form) });
            const data   = await res.json();
            if (res.ok) {
                toast(this.editId ? 'Kas diperbarui.' : 'Kas ditambah.', 'success');
                this.open = false;
                await this.load();
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                toast(errs, 'error');
            }
            this.saving = false;
        },

        async doDelete() {
            const res = await apiFetch(`/api/kas/${this.deleteTarget.id}`, { method: 'DELETE' });
            if (res.ok) { toast('Kas dihapus.', 'success'); this.confirmDelete = false; await this.load(); }
            else { const d = await res.json(); toast(d.message, 'error'); }
        },

        formatRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
    }
}
</script>
@endsection
