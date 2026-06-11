@extends('layouts.app')
@section('title', 'Master Barang')

@section('content')
<div x-data="barangApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Master Barang</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola data produk dan stok inventori</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
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
            <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Cari barang..."
                   class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-2xl h-16"></div>@endfor
    </div>

    {{-- Mobile cards --}}
    <div x-show="!loading" x-cloak class="md:hidden space-y-3">
        <template x-for="b in items" :key="b.id">
            <div class="card card-p">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="icon-wrap icon-wrap-emerald shrink-0" :class="b.stok_menipis ? 'icon-wrap-rose' : 'icon-wrap-emerald'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate" style="color:var(--color-text);" x-text="b.nama"></p>
                            <p class="text-xs" style="color:var(--color-text-muted);" x-text="b.kode + ' · ' + b.satuan"></p>
                        </div>
                    </div>
                    <span x-show="b.stok_menipis" class="badge badge-danger shrink-0 ml-2">Menipis</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs py-3 border-t border-b mb-3" style="border-color:var(--color-border);">
                    <div>
                        <p class="mb-0.5" style="color:var(--color-text-muted);">Stok</p>
                        <p class="font-bold" :style="b.stok_menipis?'color:var(--color-danger);':'color:var(--color-text);'" x-text="b.stok + ' ' + b.satuan"></p>
                    </div>
                    <div>
                        <p class="mb-0.5" style="color:var(--color-text-muted);">Harga Beli</p>
                        <p class="font-medium" style="color:var(--color-text);" x-text="formatRp(b.harga_beli)"></p>
                    </div>
                    <div>
                        <p class="mb-0.5" style="color:var(--color-text-muted);">Harga Jual</p>
                        <p class="font-medium" style="color:var(--color-primary);" x-text="formatRp(b.harga_jual)"></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="openEdit(b)" class="btn btn-sm btn-secondary flex-1 justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        Edit
                    </button>
                    <button @click="openDelete(b)" class="btn btn-sm btn-danger flex-1 justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Hapus
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Desktop table --}}
    <div x-show="!loading" x-cloak class="hidden md:block table-wrap">
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
            <tbody>
                <template x-for="(b, idx) in items" :key="b.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell font-mono text-xs" style="color:var(--color-text-muted);" x-text="b.kode"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center gap-2">
                                <span class="font-medium" style="color:var(--color-text);" x-text="b.nama"></span>
                                <span x-show="b.stok_menipis" class="badge badge-danger">Menipis</span>
                            </div>
                            
                        </td>
                        <td class="tbl-cell text-right font-semibold"
                            :style="b.stok_menipis?'color:var(--color-danger);':'color:var(--color-text);'"
                            x-text="b.stok + ' ' + b.satuan"></td>
                        <td class="tbl-cell text-right text-xs" style="color:var(--color-text-muted);" x-text="formatRp(b.harga_beli)"></td>
                        <td class="tbl-cell text-right font-medium" style="color:var(--color-primary);" x-text="formatRp(b.harga_jual)"></td>
                        <td class="tbl-cell text-center">
                            <span class="badge" :class="b.is_active ? 'badge-success' : 'badge-neutral'"
                                  x-text="b.is_active ? 'Aktif' : 'Nonaktif'"></span>
                        </td>
                        <td class="tbl-cell text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(b)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                                <button @click="openDelete(b)" title="Hapus"
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
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5"/></svg>
                        <p class="text-sm font-medium">Tidak ada barang ditemukan</p>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                Menampilkan <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span> barang
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

    {{-- Modal Form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId?'Edit Barang':'Tambah Barang'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Kode (SKU)</label>
                        <input type="text" x-model="form.kode" required class="form-input" placeholder="SKU-001"/>
                    </div>
                    <div>
                        <label class="form-label">Satuan</label>
                        <input type="text" x-model="form.satuan" required class="form-input" placeholder="kg, pcs, ltr"/>
                    </div>
                </div>
                <div>
                    <label class="form-label">Nama Barang</label>
                    <input type="text" x-model="form.nama" required class="form-input" placeholder="Nama produk"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Harga Beli (Rp)</label>
                        <input type="number" x-model="form.harga_beli" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" x-model="form.harga_jual" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                </div>
                <div>
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" x-model="form.stok_minimum" min="0" step="0.01" class="form-input" placeholder="0"/>
                </div>
                <div class="flex items-center gap-2 py-1">
                    <input type="checkbox" x-model="form.is_active" id="is_active_brg" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                    <label for="is_active_brg" class="text-sm cursor-pointer" style="color:var(--color-text);">Barang aktif</label>
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
        <x-modal title="Hapus Barang">
            <p class="text-sm mb-5" style="color:var(--color-text);">
                Hapus barang <strong x-text="deleteTarget?.nama"></strong>? Pastikan tidak ada mutasi yang terkait.
            </p>
            <div class="flex gap-3">
                <button @click="confirmDelete=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function barangApp() {
    return {
        items:[], loading:true, open:false, saving:false,
        confirmDelete:false, deleteTarget:null, editId:null,
        search: '',
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form:{kode:'',nama:'',satuan:'',harga_beli:0,harga_jual:0,stok_minimum:0,is_active:true},

        get pageRange() {
            const cur = this.meta.current_page, last = this.meta.last_page;
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) { for(let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
            else if (cur >= last-3) { pages.push(1); pages.push('...'); for(let i=last-4;i<=last;i++) pages.push(i); }
            else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
            return pages;
        },

        async load(page = 1){
            this.loading=true;
            let url=`/api/barang?page=${page}&per_page=15`;
            if(this.search) url+=`&search=${encodeURIComponent(this.search)}`;
            const res=await apiFetch(url);
            if(res?.ok){
                const d=await res.json();
                this.items=d.data||[];
                if(d.meta) this.meta=d.meta;
                else this.meta={ current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
            }
            this.loading=false;
        },

        goPage(p){ if(p!=='...'&&p>=1&&p<=this.meta.last_page) this.load(p); },

        openCreate(){this.editId=null;this.form={kode:'',nama:'',satuan:'',harga_beli:0,harga_jual:0,stok_minimum:0,is_active:true};this.open=true;},
        openEdit(b){this.editId=b.id;this.form={kode:b.kode,nama:b.nama,satuan:b.satuan,harga_beli:b.harga_beli,harga_jual:b.harga_jual,stok_minimum:b.stok_minimum,is_active:b.is_active};this.open=true;},
        openDelete(b){this.deleteTarget=b;this.confirmDelete=true;},

        async save(){
            this.saving=true;
            const url=this.editId?`/api/barang/${this.editId}`:'/api/barang';
            const method=this.editId?'PUT':'POST';
            const res=await apiFetch(url,{method,body:JSON.stringify(this.form)});
            const data=await res.json();
            if(res.ok){toast('Barang disimpan.','success');this.open=false;await this.load(this.meta.current_page);}
            else{const msg=data.errors?Object.values(data.errors).flat().join(' '):data.message;toast(msg,'error');}
            this.saving=false;
        },

        async doDelete(){
            const res=await apiFetch(`/api/barang/${this.deleteTarget.id}`,{method:'DELETE'});
            if(res.ok){toast('Barang dihapus.','success');this.confirmDelete=false;await this.load(this.meta.current_page);}
            else{const d=await res.json();toast(d.message,'error');}
        },

        formatRp(n){ return 'Rp '+Number(n).toLocaleString('id-ID'); }
    }
}
</script>
@endsection
