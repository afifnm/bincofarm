@extends('layouts.app')
@section('title', 'Kartu Stok')

@section('content')
<div x-data="kartuStokApp()" x-init="init()">

    {{-- Page header --}}
    <div class="page-header no-print">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Kartu Stok</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Riwayat pergerakan stok per barang</p>
        </div>
        <button @click="window.print()" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
            </svg>
            Cetak
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-end gap-3 mb-6 p-4 rounded-xl border no-print" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex-1 min-w-40">
            <label class="form-label">Barang</label>
            <select x-model="filterBarang" class="form-input">
                <option value="">— Pilih Barang —</option>
                <template x-for="b in barangList" :key="b.id"><option :value="b.id" x-text="b.nama"></option></template>
            </select>
        </div>
        <div class="flex-1 min-w-32">
            <label class="form-label">Dari</label>
            <input type="date" x-model="dari" class="form-input"/>
        </div>
        <div class="flex-1 min-w-32">
            <label class="form-label">Sampai</label>
            <input type="date" x-model="sampai" class="form-input"/>
        </div>
        <button @click="load()" class="btn btn-primary self-end">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            Tampilkan
        </button>
        <div class="w-full flex gap-1.5">
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
    <div x-show="loading" class="skeleton rounded-2xl h-64"></div>

    {{-- Report --}}
    <div x-show="report && !loading" x-cloak class="print-container table-wrap">

        {{-- Header --}}
        <div class="px-5 py-4 flex items-center justify-between"
             style="background:linear-gradient(135deg,var(--color-primary) 0%,var(--color-primary-hover) 100%);color:#fff;">
            <div>
                <p class="font-semibold text-sm" x-text="report?.barang?.nama"></p>
                <p class="text-xs opacity-75 mt-0.5" x-text="report?.barang?.kode + ' · ' + (report?.barang?.satuan||'')"></p>
            </div>
            <div class="text-right">
                <p class="text-xs opacity-75">Stok Akhir</p>
                <p class="font-bold text-base" x-text="(report?.stok_akhir||0) + ' ' + (report?.barang?.satuan||'')"></p>
            </div>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-3 gap-0 border-b" style="border-color:var(--color-border);">
            <div class="px-4 py-3 text-center border-r" style="border-color:var(--color-border);">
                <p class="text-xs mb-1" style="color:var(--color-text-muted);">Stok Awal</p>
                <p class="font-bold text-sm" style="color:var(--color-text);" x-text="(report?.stok_awal||0) + ' ' + (report?.barang?.satuan||'')"></p>
            </div>
            <div class="px-4 py-3 text-center border-r" style="border-color:var(--color-border);">
                <p class="text-xs mb-1" style="color:var(--color-text-muted);">Total Masuk</p>
                <p class="font-bold text-sm" style="color:var(--color-success);" x-text="(report?.total_masuk||0) + ' ' + (report?.barang?.satuan||'')"></p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-xs mb-1" style="color:var(--color-text-muted);">Total Keluar</p>
                <p class="font-bold text-sm" style="color:var(--color-danger);" x-text="(report?.total_keluar||0) + ' ' + (report?.barang?.satuan||'')"></p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="tbl-head">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor</th>
                        <th class="hidden md:table-cell">Referensi</th>
                        <th class="hidden md:table-cell">Keterangan</th>
                        <th class="text-right" style="color:#15803D;">Masuk</th>
                        <th class="text-right" style="color:#B91C1C;">Keluar</th>
                        <th class="text-right">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in report?.mutasi||[]" :key="row.id">
                        <tr class="tbl-row text-xs">
                            <td class="tbl-cell" x-text="row.tanggal"></td>
                            <td class="tbl-cell font-mono" style="color:var(--color-text-muted);" x-text="row.nomor"></td>
                            <td class="tbl-cell hidden md:table-cell" x-text="row.referensi||'—'"></td>
                            <td class="tbl-cell hidden md:table-cell" style="color:var(--color-text-muted);" x-text="row.keterangan||'—'"></td>
                            <td class="tbl-cell text-right font-medium" style="color:var(--color-success);" x-text="row.masuk > 0 ? row.masuk : '—'"></td>
                            <td class="tbl-cell text-right font-medium" style="color:var(--color-danger);" x-text="row.keluar > 0 ? row.keluar : '—'"></td>
                            <td class="tbl-cell text-right font-semibold" style="color:var(--color-text);" x-text="row.stok_berjalan"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr style="background:var(--color-bg);border-top:2px solid var(--color-border);">
                        <td colspan="4" class="tbl-cell font-bold" style="color:var(--color-text);">Stok Akhir</td>
                        <td class="tbl-cell text-right font-bold" style="color:var(--color-success);" x-text="report?.total_masuk"></td>
                        <td class="tbl-cell text-right font-bold" style="color:var(--color-danger);" x-text="report?.total_keluar"></td>
                        <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);" x-text="report?.stok_akhir"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && !report" class="text-center py-16" style="color:var(--color-text-muted);">
        <svg class="w-12 h-12 mx-auto mb-4 opacity-25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/></svg>
        <p class="text-sm font-medium">Pilih barang dan periode</p>
        <p class="text-xs mt-1">Lalu klik Tampilkan untuk melihat kartu stok</p>
    </div>
</div>

<script>
function kartuStokApp() {
    return {
        barangList:[], filterBarang:'', dari:'', sampai:'', report:null, loading:false,

        async init(){
            const now=new Date();
            this.dari=new Date(now.getFullYear(),now.getMonth(),1).toISOString().slice(0,10);
            this.sampai=now.toISOString().slice(0,10);
            const res=await apiFetch('/api/barang?per_page=100');
            if(res?.ok){const d=await res.json();this.barangList=d.data||[];}
        },

        setThisMonth(){ const n=new Date(); this.dari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.sampai=n.toISOString().slice(0,10); },
        setLastMonth(){ const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; this.dari=new Date(y,m,1).toISOString().slice(0,10); this.sampai=new Date(y,m+1,0).toISOString().slice(0,10); },
        resetDates(){ const n=new Date(); this.dari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.sampai=n.toISOString().slice(0,10); this.report=null; },

        async load(){
            if(!this.filterBarang||!this.dari||!this.sampai) return;
            this.loading=true; this.report=null;
            const url=`/api/laporan/kartu-stok?barang_id=${this.filterBarang}&dari=${this.dari}&sampai=${this.sampai}`;
            const res=await apiFetch(url);
            if(res?.ok) this.report=await res.json();
            this.loading=false;
        }
    }
}
</script>
@endsection
