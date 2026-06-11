@extends('layouts.app')
@section('title', 'Master Kas — Kategori')

@section('content')
<div x-data="kategoriApp()" x-init="load()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs mb-4" style="color:var(--color-text-muted);">
        <span>Master Kas</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-primary);">Kategori</span>
    </nav>

    {{-- Sub nav tabs --}}
    <div class="flex gap-1.5 mb-5">
        <a href="{{ route('kas') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);"
           onmouseover="this.style.background='var(--color-bg)'"
           onmouseout="this.style.background='var(--color-surface)'">
            Rekening
        </a>
        <a href="{{ route('kategori') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-primary);color:#fff;">
            Kategori
        </a>
    </div>

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
            Tambah Kategori
        </button>
    </div>

    {{-- Filter + Search bar --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <div class="relative flex-1 min-w-48 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Cari kategori..."
                   class="form-input pl-9"/>
        </div>
        <button @click="filterJenis=''" class="filter-pill" :class="filterJenis==='' ? 'active' : ''">Semua</button>
        <button @click="filterJenis='masuk'; load()" class="filter-pill" :class="filterJenis==='masuk' ? 'active-success' : ''">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
            Masuk
        </button>
        <button @click="filterJenis='keluar'; load()" class="filter-pill" :class="filterJenis==='keluar' ? 'active-danger' : ''">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
            Keluar
        </button>
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
                    <th>Nama Kategori</th>
                    <th class="text-center">Jenis</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, idx) in items" :key="item.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell font-medium" style="color:var(--color-text);" x-text="item.nama"></td>
                        <td class="tbl-cell text-center">
                            <span class="badge" :class="item.jenis==='masuk' ? 'badge-success' : 'badge-danger'">
                                <svg x-show="item.jenis==='masuk'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                                <svg x-show="item.jenis==='keluar'" x-cloak class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                                <span x-text="item.jenis_label"></span>
                            </span>
                        </td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openEdit(item)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                                <button @click="!item.in_use && openDelete(item)"
                                        :title="item.in_use ? 'Kategori sudah digunakan di transaksi' : 'Hapus'"
                                        :disabled="item.in_use"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        :style="item.in_use ? 'color:var(--color-border);cursor:not-allowed;' : 'color:var(--color-text-muted);'"
                                        x-on:mouseover="!item.in_use && (($el.style.background='#FEE2E2') || ($el.style.color='#B91C1C'))"
                                        x-on:mouseout="!item.in_use && (($el.style.background='') || ($el.style.color='var(--color-text-muted)'))">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && items.length===0">
                    <td colspan="4" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">
                        <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/></svg>
                        Tidak ada kategori.
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span>
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
        <x-modal title="Hapus Kategori" closeExpr="confirmDelete = false">
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
        confirmDelete:false, deleteTarget:null, editId:null,
        search:'', filterJenis:'',
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form:{nama:'',jenis:'masuk'},

        get pageRange() {
            const cur = this.meta.current_page, last = this.meta.last_page;
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) { for(let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
            else if (cur >= last-3) { pages.push(1); pages.push('...'); for(let i=last-4;i<=last;i++) pages.push(i); }
            else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
            return pages;
        },

        async load(page = 1) {
            this.loading = true;
            let url = `/api/kategori-transaksi?page=${page}&per_page=15`;
            if (this.search) url += `&search=${encodeURIComponent(this.search)}`;
            if (this.filterJenis) url += `&jenis=${this.filterJenis}`;
            const res = await apiFetch(url);
            if(res?.ok){
                const d = await res.json();
                this.items = d.data || [];
                if (d.meta) this.meta = d.meta;
                else this.meta = { current_page: d.current_page, last_page: d.last_page, from: d.from, to: d.to, total: d.total };
            }
            this.loading = false;
        },

        goPage(p) { if (p !== '...' && p >= 1 && p <= this.meta.last_page) this.load(p); },

        openCreate(){this.editId=null;this.form={nama:'',jenis:'masuk'};this.open=true;},
        openEdit(item){this.editId=item.id;this.form={nama:item.nama,jenis:item.jenis};this.open=true;},
        openDelete(item){this.deleteTarget=item;this.confirmDelete=true;},

        async save(){
            this.saving=true;
            const url=this.editId?`/api/kategori-transaksi/${this.editId}`:'/api/kategori-transaksi';
            const method=this.editId?'PUT':'POST';
            const res=await apiFetch(url,{method,body:JSON.stringify(this.form)});
            const data=await res.json();
            if(res.ok){toast('Kategori disimpan.','success');this.open=false;await this.load(this.meta.current_page);}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },
        async doDelete(){
            const res=await apiFetch(`/api/kategori-transaksi/${this.deleteTarget.id}`,{method:'DELETE'});
            if(res.ok){toast('Kategori dihapus.','success');this.confirmDelete=false;await this.load(this.meta.current_page);}
            else{const d=await res.json();toast(d.message,'error');}
        }
    }
}
</script>
@endsection
