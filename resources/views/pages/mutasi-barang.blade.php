@extends('layouts.app')
@section('title', 'Mutasi Barang')

@section('content')
<div x-data="mutasiApp()" x-init="init()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Mutasi Barang</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Pencatatan keluar masuk dan penyesuaian stok</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="openCreate('masuk')" class="btn btn-sm btn-success">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                Masuk
            </button>
            <button @click="openCreate('keluar')" class="btn btn-sm btn-danger">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                Keluar
            </button>
            <button @click="openCreate('penyesuaian')" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                Sesuaian
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 p-4 rounded-xl border" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-40">
                <label class="form-label">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Nomor / keterangan..." class="form-input pl-9"/>
                </div>
            </div>
            <div class="flex-1 min-w-36">
                <label class="form-label">Barang</label>
                <select x-model="filterBarang" @change="load()" class="form-input">
                    <option value="">Semua Barang</option>
                    <template x-for="b in barangList" :key="b.id"><option :value="b.id" x-text="b.nama"></option></template>
                </select>
            </div>
            <div class="flex-1 min-w-28">
                <label class="form-label">Dari</label>
                <input type="date" x-model="filterDari" @change="load()" class="form-input"/>
            </div>
            <div class="flex-1 min-w-28">
                <label class="form-label">Sampai</label>
                <input type="date" x-model="filterSampai" @change="load()" class="form-input"/>
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5 mt-3">
            <button type="button" @click="setThisMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Bulan Ini
            </button>
            <button type="button" @click="setLastMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Bulan Lalu
            </button>
            <button type="button" @click="resetDates()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-14"></div>@endfor
    </div>

    {{-- Table --}}
    <div x-show="!loading" x-cloak class="table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Mutasi</th>
                    <th class="hidden md:table-cell">Barang</th>
                    <th class="hidden lg:table-cell">Input Oleh</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right hidden md:table-cell">Stok Sisa</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(m, idx) in items" :key="m.id">
                    <tr class="tbl-row" :class="m.is_void ? 'void-row' : ''">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                     :class="m.tipe==='masuk' ? 'icon-wrap-emerald' : m.tipe==='keluar' ? 'icon-wrap-rose' : 'icon-wrap-blue'">
                                    <svg x-show="m.tipe==='masuk'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                                    <svg x-show="m.tipe==='keluar'" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                                    <svg x-show="m.tipe==='penyesuaian'" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-xs" style="color:var(--color-text);" x-text="m.nomor"></p>
                                    <p class="text-xs" style="color:var(--color-text-muted);"
                                       x-text="m.tanggal + (m.keterangan ? ' — ' + m.keterangan : '')"></p>
                                    {{-- Show barang on mobile --}}
                                    <p class="text-xs md:hidden mt-0.5 font-medium" style="color:var(--color-text);" x-text="m.barang?.nama || '-'"></p>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-cell hidden md:table-cell text-xs">
                            <p class="font-medium" style="color:var(--color-text);" x-text="m.barang?.nama || '-'"></p>
                            <p style="color:var(--color-text-muted);" x-text="m.referensi ? 'Ref: '+m.referensi : ''"></p>
                        </td>
                        <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);" x-text="m.user?.name || '—'"></td>
                        <td class="tbl-cell text-right font-bold text-sm"
                            :style="m.tipe==='masuk'?'color:var(--color-success);':m.tipe==='keluar'?'color:var(--color-danger);':'color:#0369A1;'"
                            x-text="(m.tipe==='masuk'?'+':m.tipe==='keluar'?'-':'±') + m.qty + ' ' + (m.barang?.satuan||'')">
                        </td>
                        <td class="tbl-cell text-right hidden md:table-cell text-xs font-semibold" style="color:var(--color-text);"
                            x-text="m.stok_setelah + ' ' + (m.barang?.satuan||'')"></td>
                        <td class="tbl-cell text-center">
                            <span x-show="m.is_void" class="badge badge-danger">VOID</span>
                            <span x-show="!m.is_void" class="badge badge-success">Aktif</span>
                        </td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center">
                                <button x-show="!m.is_void" @click="openVoid(m)" title="Void"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && items.length===0">
                    <td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                        <p class="text-sm font-medium">Tidak ada mutasi</p>
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
        <x-modal>
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);"
                x-text="form.tipe==='masuk'?'Barang Masuk':form.tipe==='keluar'?'Barang Keluar':'Penyesuaian Stok'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div x-show="periodeClosed" x-cloak
                     class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs"
                     style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Periode ini sudah ditutup.
                </div>
                <div>
                    <label class="form-label">Barang</label>
                    <select x-model="form.barang_id" required class="form-input">
                        <template x-for="b in barangList" :key="b.id">
                            <option :value="b.id" x-text="b.nama + ' (stok: ' + b.stok + ' ' + b.satuan + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" x-model="form.tanggal" @change="checkPeriode()" required class="form-input"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label" x-text="form.tipe==='penyesuaian'?'Stok Baru':'Qty'"></label>
                        <input type="number" x-model="form.qty" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="form-label">Harga Satuan (Rp)</label>
                        <input type="number" x-model="form.harga_satuan" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                </div>
                <div>
                    <label class="form-label">Referensi (No. Faktur)</label>
                    <input type="text" x-model="form.referensi" class="form-input" placeholder="Opsional"/>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input type="text" x-model="form.keterangan" class="form-input" placeholder="Opsional"/>
                </div>
                {{-- Link to Kas --}}
                <div x-show="form.tipe!=='penyesuaian'" class="border-t pt-4" style="border-color:var(--color-border);">
                    <div class="flex items-center gap-2 mb-3">
                        <input type="checkbox" x-model="linkKas" id="link_kas" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                        <label for="link_kas" class="text-sm cursor-pointer" style="color:var(--color-text);">
                            Buat transaksi kas otomatis
                        </label>
                    </div>
                    <div x-show="linkKas" x-cloak>
                        <label class="form-label">Pilih Kas</label>
                        <select x-model="form.kas_id" class="form-input">
                            <option value="">— Pilih Kas —</option>
                            <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn flex-1 justify-center text-white"
                            :style="form.tipe==='masuk'?'background:var(--color-success);':form.tipe==='keluar'?'background:var(--color-danger);':'background:#0369A1;'">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Void --}}
    <div x-show="confirmVoid" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Void Mutasi">
            <p class="text-sm mb-5" style="color:var(--color-text);">
                Void mutasi <strong x-text="voidTarget?.nomor"></strong>? Stok akan dikembalikan.
            </p>
            <div class="flex gap-3">
                <button @click="confirmVoid=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doVoid()" class="btn btn-danger flex-1 justify-center">Void</button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function mutasiApp() {
    return {
        items:[], loading:true, open:false, saving:false,
        confirmVoid:false, voidTarget:null, periodeClosed:false, linkKas:false,
        barangList:[], kasList:[],
        search:'', filterBarang:'', filterDari:'', filterSampai:'',
        meta:{ current_page:1, last_page:1, from:1, to:1, total:0 },
        form:{barang_id:'',tanggal:'',tipe:'masuk',qty:'',harga_satuan:0,referensi:'',keterangan:'',kas_id:''},

        get pageRange() {
            const cur=this.meta.current_page, last=this.meta.last_page;
            if(last<=7) return Array.from({length:last},(_,i)=>i+1);
            const pages=[];
            if(cur<=4){for(let i=1;i<=5;i++) pages.push(i);pages.push('...');pages.push(last);}
            else if(cur>=last-3){pages.push(1);pages.push('...');for(let i=last-4;i<=last;i++) pages.push(i);}
            else{pages.push(1);pages.push('...');pages.push(cur-1);pages.push(cur);pages.push(cur+1);pages.push('...');pages.push(last);}
            return pages;
        },

        async init(){
            const [bRes,kRes]=await Promise.all([apiFetch('/api/barang?per_page=200'),apiFetch('/api/kas?per_page=100')]);
            if(bRes?.ok){const d=await bRes.json();this.barangList=d.data||[];}
            if(kRes?.ok){const d=await kRes.json();this.kasList=d.data||[];}
            const now=new Date();
            this.filterDari=new Date(now.getFullYear(),now.getMonth(),1).toISOString().slice(0,10);
            this.filterSampai=now.toISOString().slice(0,10);
            await this.load();
        },

        setThisMonth(){ const n=new Date(); this.filterDari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.filterSampai=n.toISOString().slice(0,10); this.load(); },
        setLastMonth(){ const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; this.filterDari=new Date(y,m,1).toISOString().slice(0,10); this.filterSampai=new Date(y,m+1,0).toISOString().slice(0,10); this.load(); },
        resetDates(){ this.filterDari=''; this.filterSampai=''; this.filterBarang=''; this.search=''; this.load(); },

        async load(page=1){
            this.loading=true;
            let url=`/api/mutasi-barang?page=${page}&per_page=20`;
            if(this.filterBarang) url+=`&barang_id=${this.filterBarang}`;
            if(this.filterDari) url+=`&dari=${this.filterDari}`;
            if(this.filterSampai) url+=`&sampai=${this.filterSampai}`;
            if(this.search) url+=`&search=${encodeURIComponent(this.search)}`;
            const res=await apiFetch(url);
            if(res?.ok){
                const d=await res.json();
                this.items=d.data||[];
                if(d.meta) this.meta=d.meta;
                else this.meta={current_page:d.current_page,last_page:d.last_page,from:d.from,to:d.to,total:d.total};
            }
            this.loading=false;
        },

        goPage(p){ if(p!=='...'&&p>=1&&p<=this.meta.last_page) this.load(p); },

        openCreate(tipe){
            this.form={barang_id:this.barangList[0]?.id||'',tanggal:new Date().toISOString().slice(0,10),tipe,qty:'',harga_satuan:0,referensi:'',keterangan:'',kas_id:''};
            this.linkKas=false; this.periodeClosed=false; this.open=true;
        },

        async checkPeriode(){
            if(!this.form.tanggal||!this.form.barang_id) return;
            const res=await apiFetch(`/api/periode/check?tanggal=${this.form.tanggal}`);
            if(res?.ok){const d=await res.json();this.periodeClosed=d.is_closed;}
        },

        async save(){
            this.saving=true;
            const payload={...this.form};
            if(!this.linkKas) delete payload.kas_id;
            const res=await apiFetch('/api/mutasi-barang',{method:'POST',body:JSON.stringify(payload)});
            const data=await res.json();
            if(res.ok){toast('Mutasi berhasil.','success');this.open=false;await this.load(this.meta.current_page);}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        openVoid(m){this.voidTarget=m;this.confirmVoid=true;},

        async doVoid(){
            const res=await apiFetch(`/api/mutasi-barang/${this.voidTarget.id}`,{method:'DELETE'});
            const data=await res.json();
            if(res.ok){toast('Mutasi di-void.','success');this.confirmVoid=false;await this.load(this.meta.current_page);}
            else{toast(data.message,'error');}
        }
    }
}
</script>
@endsection
