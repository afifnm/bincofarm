@extends('layouts.app')
@section('title', 'Laporan Cashflow')

@section('content')
<div x-data="cashflowApp()" x-init="init()">

    {{-- Page header --}}
    <div class="page-header no-print">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Laporan Cashflow</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Rekap arus kas masuk dan keluar per periode</p>
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
        <div class="flex-1 min-w-32">
            <label class="form-label">Kas</label>
            <select x-model="filterKas" class="form-input">
                <option value="">Semua Kas</option>
                <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
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
            <button type="button" @click="setThisMonth()" class="filter-pill text-xs px-3 py-1">Bulan Ini</button>
            <button type="button" @click="setLastMonth()" class="filter-pill text-xs px-3 py-1">Bulan Lalu</button>
            <button type="button" @click="resetDates()" class="filter-pill text-xs px-3 py-1">Reset</button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-4">
        <div class="skeleton rounded-2xl h-48"></div>
    </div>

    {{-- Reports --}}
    <template x-for="(report, idx) in reports" :key="idx">
        <div class="mb-8 print-container table-wrap">

            {{-- Report header --}}
            <div class="px-5 py-4 flex items-center justify-between"
                 style="background:linear-gradient(135deg,var(--color-primary) 0%,var(--color-primary-hover) 100%);color:#fff;">
                <div>
                    <p class="font-semibold text-sm" x-text="report.kas?.nama || 'Semua Kas'"></p>
                    <p class="text-xs opacity-75 mt-0.5">Laporan Cashflow</p>
                </div>
                <div class="text-right">
                    <p class="text-xs opacity-75">Saldo Akhir</p>
                    <p class="font-bold text-base" x-text="formatRp(report.saldo_akhir)"></p>
                </div>
            </div>

            {{-- Summary strip --}}
            <div class="grid grid-cols-3 gap-0 border-b" style="border-color:var(--color-border);">
                <div class="px-4 py-3 text-center border-r" style="border-color:var(--color-border);">
                    <p class="text-xs mb-1" style="color:var(--color-text-muted);">Saldo Awal</p>
                    <p class="font-bold text-sm" style="color:var(--color-text);" x-text="formatRp(report.saldo_awal)"></p>
                </div>
                <div class="px-4 py-3 text-center border-r" style="border-color:var(--color-border);">
                    <p class="text-xs mb-1" style="color:var(--color-text-muted);">Total Masuk</p>
                    <p class="font-bold text-sm" style="color:var(--color-success);" x-text="formatRp(report.total_masuk)"></p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="text-xs mb-1" style="color:var(--color-text-muted);">Total Keluar</p>
                    <p class="font-bold text-sm" style="color:var(--color-danger);" x-text="formatRp(report.total_keluar)"></p>
                </div>
            </div>

            {{-- Transaction table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="tbl-head">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th class="hidden md:table-cell">Kategori</th>
                            <th class="hidden md:table-cell">Keterangan</th>
                            <th class="text-right" style="color:#15803D;">Masuk</th>
                            <th class="text-right" style="color:#B91C1C;">Keluar</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="trx in report.transaksi" :key="trx.id">
                            <tr class="tbl-row text-xs">
                                <td class="tbl-cell" x-text="formatDate(trx.tanggal)"></td>
                                <td class="tbl-cell font-mono" style="color:var(--color-text-muted);" x-text="trx.nomor"></td>
                                <td class="tbl-cell hidden md:table-cell" x-text="trx.kategori||'-'"></td>
                                <td class="tbl-cell hidden md:table-cell" style="color:var(--color-text-muted);" x-text="trx.keterangan||'-'"></td>
                                <td class="tbl-cell text-right font-medium" style="color:var(--color-success);" x-text="trx.masuk > 0 ? formatRp(trx.masuk) : '—'"></td>
                                <td class="tbl-cell text-right font-medium" style="color:var(--color-danger);" x-text="trx.keluar > 0 ? formatRp(trx.keluar) : '—'"></td>
                                <td class="tbl-cell text-right font-semibold" style="color:var(--color-text);" x-text="formatRp(trx.saldo_berjalan)"></td>
                            </tr>
                        </template>
                        <tr x-show="!report.transaksi || report.transaksi.length === 0">
                            <td colspan="7" class="tbl-cell text-center py-6" style="color:var(--color-text-muted);">Tidak ada transaksi.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--color-bg);border-top:2px solid var(--color-border);">
                            <td colspan="4" class="tbl-cell font-bold" style="color:var(--color-text);">Total</td>
                            <td class="tbl-cell text-right font-bold" style="color:var(--color-success);" x-text="formatRp(report.total_masuk)"></td>
                            <td class="tbl-cell text-right font-bold" style="color:var(--color-danger);" x-text="formatRp(report.total_keluar)"></td>
                            <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);" x-text="formatRp(report.saldo_akhir)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </template>

    <div x-show="!loading && reports.length === 0" class="text-center py-16" style="color:var(--color-text-muted);">
        <svg class="w-12 h-12 mx-auto mb-4 opacity-25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
        <p class="text-sm font-medium">Atur filter dan klik Tampilkan</p>
    </div>
</div>

<script>
function cashflowApp() {
    return {
        kasList:[], filterKas:'', dari:'', sampai:'', reports:[], loading:false,

        async init(){
            const now = new Date();
            const params = new URLSearchParams(window.location.search);
            this.dari   = params.get('dari')  || new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10);
            this.sampai = params.get('sampai') || now.toISOString().slice(0,10);
            this.filterKas = params.get('kas_id') || '';
            const res = await apiFetch('/api/kas?per_page=100');
            if(res?.ok){const d=await res.json();this.kasList=d.data||[];}
            await this.load();
        },

        setThisMonth(){ const n=new Date(); this.dari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.sampai=n.toISOString().slice(0,10); this.load(); },
        setLastMonth(){ const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; this.dari=new Date(y,m,1).toISOString().slice(0,10); this.sampai=new Date(y,m+1,0).toISOString().slice(0,10); this.load(); },
        resetDates(){ this.dari=''; this.sampai=''; this.filterKas=''; this.reports=[]; },

        async load(){
            if(!this.dari||!this.sampai) return;
            this.loading=true;
            let url=`/api/laporan/cashflow?dari=${this.dari}&sampai=${this.sampai}`;
            if(this.filterKas) url+=`&kas_id=${this.filterKas}`;
            const res=await apiFetch(url);
            if(res?.ok){
                const data=await res.json();
                this.reports = Array.isArray(data) ? data : [data];
            }
            this.loading=false;
        },
        formatRp(n){ return 'Rp '+Number(n||0).toLocaleString('id-ID'); }
    }
}
</script>

<style>
@media print {
    #print-header { display: block !important; }
    .no-print { display: none !important; }
}
</style>
@endsection
