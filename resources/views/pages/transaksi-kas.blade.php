@extends('layouts.app')
@section('title', 'Transaksi Kas')

@section('content')
<div x-data="transaksiApp()" x-init="init()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Transaksi Kas</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Catat pemasukan, pengeluaran, dan transfer</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <button @click="goCashflow()" class="btn btn-sm btn-secondary" x-show="!filterVoid">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Cashflow
            </button>
            <button @click="openCreate('masuk')" class="btn btn-sm btn-success" x-show="!filterVoid">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                Masuk
            </button>
            <button @click="openCreate('keluar')" class="btn btn-sm btn-danger" x-show="!filterVoid">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                Keluar
            </button>
            <button @click="openTransfer()" class="btn btn-sm btn-secondary" x-show="!filterVoid">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Transfer
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
                <label class="form-label">Kas</label>
                <select x-model="filterKas" @change="load()" class="form-input">
                    <option value="">Semua Kas</option>
                    <template x-for="k in kasList" :key="k.id">
                        <option :value="k.id" x-text="k.nama"></option>
                    </template>
                </select>
            </div>
            <div class="flex-1 min-w-32">
                <label class="form-label">Dari</label>
                <input type="date" x-model="filterDari" @change="load()" class="form-input"/>
            </div>
            <div class="flex-1 min-w-32">
                <label class="form-label">Sampai</label>
                <input type="date" x-model="filterSampai" @change="load()" class="form-input"/>
            </div>
        </div>
        {{-- Date shortcuts --}}
        <div class="flex flex-wrap gap-1.5 mt-3">
            <button type="button" @click="setThisMonth()" class="filter-pill text-xs px-3 py-1" x-show="!filterVoid">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Bulan Ini
            </button>
            <button type="button" @click="setLastMonth()" class="filter-pill text-xs px-3 py-1" x-show="!filterVoid">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Bulan Lalu
            </button>
            <button type="button" @click="resetDates()" class="filter-pill text-xs px-3 py-1" x-show="!filterVoid">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset
            </button>
            <button type="button" @click="filterVoid=!filterVoid; load()" class="filter-pill text-xs px-3 py-1"
                    :style="filterVoid ? 'background:#FEE2E2;color:#B91C1C;border-color:#FECACA;font-weight:600;' : ''">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Void
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<5;$i++)
        <div class="skeleton rounded-xl h-14"></div>
        @endfor
    </div>

    {{-- Table --}}
    <div x-show="!loading" x-cloak class="table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Transaksi</th>
                    <th class="hidden md:table-cell">Kas</th>
                    <th class="hidden lg:table-cell">Input Oleh</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(trx, idx) in items" :key="trx.id">
                    <tr class="tbl-row" :class="trx.is_void ? 'void-row' : ''">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                     :class="['masuk','transfer_masuk'].includes(trx.tipe) ? 'icon-wrap-emerald' : 'icon-wrap-rose'">
                                    <svg x-show="['masuk','transfer_masuk'].includes(trx.tipe)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                                    <svg x-show="!['masuk','transfer_masuk'].includes(trx.tipe)" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-xs" style="color:var(--color-text);" x-text="trx.nomor"></p>
                                    <p class="text-xs" style="color:var(--color-text-muted);"
                                       x-text="formatDate(trx.tanggal) + (trx.keterangan ? ' — ' + trx.keterangan : '')"></p>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);">
                            <div x-text="trx.kas?.nama || '-'"></div>
                            <div x-show="trx.kategori" class="badge badge-neutral mt-0.5" x-text="trx.kategori?.nama"></div>
                        </td>
                        <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);" x-text="trx.user?.name || '—'"></td>
                        <td class="tbl-cell text-right font-bold text-sm"
                            :style="['masuk','transfer_masuk'].includes(trx.tipe)?'color:var(--color-success);':'color:var(--color-danger);'"
                            x-text="((['masuk','transfer_masuk'].includes(trx.tipe))?'+':'-') + formatRp(trx.jumlah)">
                        </td>
                        <td class="tbl-cell text-center">
                            <span x-show="trx.is_void" class="badge badge-danger">VOID</span>
                            <span x-show="!trx.is_void" class="badge badge-success">Aktif</span>
                        </td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center">
                                <button x-show="!trx.is_void" @click="openVoid(trx)" title="Void"
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
                    <td colspan="7" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/></svg>
                        <p class="text-sm font-medium">Tidak ada transaksi</p>
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

    {{-- Modal Transaksi --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);"
                x-text="form.tipe==='masuk'?'Transaksi Masuk':'Transaksi Keluar'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div x-show="periodeClosed" x-cloak
                     class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs"
                     style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Periode ini sudah ditutup. Transaksi akan ditolak.
                </div>
                <div>
                    <label class="form-label">Kas</label>
                    <select x-model="form.kas_id" required class="form-input">
                        <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" x-model="form.tanggal" @change="checkPeriode()" required class="form-input"/>
                </div>
                <div>
                    <label class="form-label">Kategori</label>
                    <select x-model="form.kategori_id" class="form-input">
                        <option value="">— Pilih Kategori —</option>
                        <template x-for="k in kategoriFiltered" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" x-model="form.jumlah" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input type="text" x-model="form.keterangan" class="form-input" placeholder="Opsional"/>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn flex-1 justify-center text-white"
                            :style="form.tipe==='masuk'?'background:var(--color-success);':'background:var(--color-danger);'">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Transfer --}}
    <div x-show="openTrf" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Transfer Antar Kas" closeExpr="openTrf = false">
            <form @submit.prevent="saveTransfer" class="space-y-4">
                <div x-show="periodeClosed" x-cloak
                     class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs"
                     style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Periode ini sudah ditutup.
                </div>
                <div>
                    <label class="form-label">Kas Asal</label>
                    <select x-model="trfForm.kas_asal_id" required class="form-input">
                        <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Kas Tujuan</label>
                    <select x-model="trfForm.kas_tujuan_id" required class="form-input">
                        <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" x-model="trfForm.tanggal" @change="checkPeriode()" required class="form-input"/>
                </div>
                <div>
                    <label class="form-label">Jumlah (Rp)</label>
                    <input type="number" x-model="trfForm.jumlah" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input type="text" x-model="trfForm.keterangan" class="form-input" placeholder="Opsional"/>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openTrf=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                        <span x-show="!saving">Transfer</span>
                        <span x-show="saving" x-cloak>Memproses...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Void --}}
    <div x-show="confirmVoid" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Void Transaksi" closeExpr="confirmVoid = false">
            <p class="text-sm mb-5" style="color:var(--color-text);">
                Void transaksi <strong x-text="voidTarget?.nomor"></strong>? Saldo akan dikembalikan.
            </p>
            <div class="flex gap-3">
                <button @click="confirmVoid=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doVoid()" class="btn btn-danger flex-1 justify-center">Void</button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function transaksiApp() {
    return {
        items:[], loading:true, open:false, openTrf:false, saving:false,
        confirmVoid:false, voidTarget:null, periodeClosed:false,
        kasList:[], kategoriList:[],
        search:'', filterKas:'', filterDari:'', filterSampai:'', filterVoid:false,
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form:{kas_id:'',tanggal:'',tipe:'masuk',kategori_id:'',jumlah:'',keterangan:''},
        trfForm:{kas_asal_id:'',kas_tujuan_id:'',tanggal:'',jumlah:'',keterangan:''},

        get kategoriFiltered(){ return this.kategoriList.filter(k=>k.jenis===this.form.tipe); },

        get pageRange() {
            const cur = this.meta.current_page, last = this.meta.last_page;
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = [];
            if (cur <= 4) { for(let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
            else if (cur >= last-3) { pages.push(1); pages.push('...'); for(let i=last-4;i<=last;i++) pages.push(i); }
            else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
            return pages;
        },

        async init(){
            const now = new Date();
            this.filterDari = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
            this.filterSampai = now.toISOString().slice(0,10);
            const [kRes,katRes]=await Promise.all([apiFetch('/api/kas?per_page=100'),apiFetch('/api/kategori-transaksi?per_page=100')]);
            if(kRes?.ok){const d=await kRes.json();this.kasList=d.data||[];}
            if(katRes?.ok){const d=await katRes.json();this.kategoriList=d.data||[];}
            await this.load();
        },

        async load(page = 1){
            this.loading=true;
            let url=`/api/transaksi-kas?page=${page}&per_page=20`;
            if(this.filterKas) url+=`&kas_id=${this.filterKas}`;
            if(this.filterDari) url+=`&dari=${this.filterDari}`;
            if(this.filterSampai) url+=`&sampai=${this.filterSampai}`;
            if(this.search) url+=`&search=${encodeURIComponent(this.search)}`;
            if(this.filterVoid) url+=`&hanya_void=1`;
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

        setThisMonth(){ const n=new Date(); this.filterDari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.filterSampai=n.toISOString().slice(0,10); this.load(); },
        setLastMonth(){ const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; this.filterDari=new Date(y,m,1).toISOString().slice(0,10); this.filterSampai=new Date(y,m+1,0).toISOString().slice(0,10); this.load(); },
        resetDates(){ this.filterDari=''; this.filterSampai=''; this.load(); },

        openCreate(tipe){
            this.form={kas_id:this.kasList[0]?.id||'',tanggal:new Date().toISOString().slice(0,10),tipe,kategori_id:'',jumlah:'',keterangan:''};
            this.periodeClosed=false; this.open=true;
        },

        openTransfer(){
            this.trfForm={kas_asal_id:this.kasList[0]?.id||'',kas_tujuan_id:this.kasList[1]?.id||'',tanggal:new Date().toISOString().slice(0,10),jumlah:'',keterangan:''};
            this.periodeClosed=false; this.openTrf=true;
        },

        async checkPeriode(){
            const tanggal=this.open?this.form.tanggal:this.trfForm.tanggal;
            const kasId=this.open?this.form.kas_id:this.trfForm.kas_asal_id;
            if(!tanggal) return;
            const res=await apiFetch(`/api/periode/check?tanggal=${tanggal}&kas_id=${kasId}`);
            if(res?.ok){const d=await res.json();this.periodeClosed=d.is_closed;}
        },

        async save(){
            this.saving=true;
            const res=await apiFetch('/api/transaksi-kas',{method:'POST',body:JSON.stringify(this.form)});
            const data=await res.json();
            if(res.ok){toast('Transaksi berhasil.','success');this.open=false;await this.load(this.meta.current_page);}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        async saveTransfer(){
            this.saving=true;
            const res=await apiFetch('/api/transaksi-kas/transfer',{method:'POST',body:JSON.stringify(this.trfForm)});
            const data=await res.json();
            if(res.ok){toast('Transfer berhasil.','success');this.openTrf=false;await this.load(this.meta.current_page);}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        openVoid(trx){this.voidTarget=trx;this.confirmVoid=true;},

        async doVoid(){
            const res=await apiFetch(`/api/transaksi-kas/${this.voidTarget.id}`,{method:'DELETE'});
            const data=await res.json();
            if(res.ok){toast('Transaksi di-void.','success');this.confirmVoid=false;await this.load(this.meta.current_page);}
            else{toast(data.message||'Gagal.','error');}
        },

        goCashflow(){
            let url = '/cashflow?';
            if(this.filterDari) url += `dari=${this.filterDari}&`;
            if(this.filterSampai) url += `sampai=${this.filterSampai}&`;
            if(this.filterKas) url += `kas_id=${this.filterKas}&`;
            window.open(url.replace(/&$/, ''), '_blank');
        },

        formatRp(n){ return 'Rp ' + Number(n||0).toLocaleString('id-ID'); }
    }
}
</script>
@endsection
