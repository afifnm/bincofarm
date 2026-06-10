@extends('layouts.app')
@section('title', 'Kategori Transaksi')

@section('content')
<div x-data="kategoriApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Kategori Transaksi</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola kategori pemasukan dan pengeluaran</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah
        </button>
    </div>

    {{-- Filter tabs --}}
    <div class="flex gap-2 mb-5 flex-wrap">
        <button @click="filter=''"
                class="filter-pill"
                :class="filter==='' ? 'active' : ''">Semua</button>
        <button @click="filter='masuk'"
                class="filter-pill"
                :class="filter==='masuk' ? 'active-success' : ''">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
            Masuk
        </button>
        <button @click="filter='keluar'"
                class="filter-pill"
                :class="filter==='keluar' ? 'active-danger' : ''">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
            Keluar
        </button>
    </div>

    {{-- Table --}}
    <div class="table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th>Nama Kategori</th>
                    <th>Jenis</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="item in filtered" :key="item.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell font-medium" style="color:var(--color-text);" x-text="item.nama"></td>
                        <td class="tbl-cell">
                            <span class="badge"
                                  :class="item.jenis==='masuk' ? 'badge-success' : 'badge-danger'"
                                  x-text="item.jenis_label"></span>
                        </td>
                        <td class="tbl-cell text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="openEdit(item)" class="btn btn-sm btn-secondary">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                    Edit
                                </button>
                                <button @click="openDelete(item)" class="btn btn-sm btn-danger">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && filtered.length===0">
                    <td colspan="3" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">
                        <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z"/></svg>
                        Tidak ada kategori.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Modal Form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="">
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId?'Edit Kategori':'Tambah Kategori'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div>
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" x-model="form.nama" required class="form-input" placeholder="cth. Pembelian BBM"/>
                </div>
                <div>
                    <label class="form-label">Jenis</label>
                    <select x-model="form.jenis" class="form-input">
                        <option value="masuk">Masuk (Pemasukan)</option>
                        <option value="keluar">Keluar (Pengeluaran)</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Hapus --}}
    <div x-show="confirmDelete" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Hapus Kategori">
            <p class="text-sm mb-5" style="color:var(--color-text);">
                Hapus kategori <strong x-text="deleteTarget?.nama"></strong>? Pastikan tidak ada transaksi yang menggunakan kategori ini.
            </p>
            <div class="flex gap-3">
                <button @click="confirmDelete=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function kategoriApp() {
    return {
        items:[], loading:true, open:false, saving:false,
        confirmDelete:false, deleteTarget:null, editId:null, filter:'',
        form:{nama:'',jenis:'masuk'},
        get filtered() { return this.filter ? this.items.filter(i=>i.jenis===this.filter) : this.items; },

        async load() {
            this.loading=true;
            const res = await apiFetch('/api/kategori-transaksi?per_page=100');
            if(res?.ok){const d=await res.json();this.items=d.data||[];}
            this.loading=false;
        },
        openCreate(){this.editId=null;this.form={nama:'',jenis:'masuk'};this.open=true;},
        openEdit(item){this.editId=item.id;this.form={nama:item.nama,jenis:item.jenis};this.open=true;},
        openDelete(item){this.deleteTarget=item;this.confirmDelete=true;},
        async save(){
            this.saving=true;
            const url=this.editId?`/api/kategori-transaksi/${this.editId}`:'/api/kategori-transaksi';
            const method=this.editId?'PUT':'POST';
            const res=await apiFetch(url,{method,body:JSON.stringify(this.form)});
            const data=await res.json();
            if(res.ok){toast('Kategori disimpan.','success');this.open=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },
        async doDelete(){
            const res=await apiFetch(`/api/kategori-transaksi/${this.deleteTarget.id}`,{method:'DELETE'});
            if(res.ok){toast('Kategori dihapus.','success');this.confirmDelete=false;await this.load();}
            else{const d=await res.json();toast(d.message,'error');}
        }
    }
}
</script>
@endsection
