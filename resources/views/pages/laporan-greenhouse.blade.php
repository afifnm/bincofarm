@extends('layouts.app')
@section('title', 'Greenhouse — Laporan')

@section('content')
<div x-data="laporanGhApp()" x-init="init()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs mb-4" style="color:var(--color-text-muted);">
        <span>Greenhouse</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-primary);">Laporan</span>
    </nav>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Laporan Greenhouse</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Rekap panen, penjualan, dan perbandingan stok</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <select x-model="filter.greenhouse_id" @change="loadAll()" class="form-input w-auto min-w-[150px]">
            <option value="">Semua GH</option>
            <template x-for="gh in greenhouses" :key="gh.id">
                <option :value="gh.id" x-text="gh.nama"></option>
            </template>
        </select>
        <input type="date" x-model="filter.dari" @change="loadAll()" class="form-input w-auto" placeholder="Dari"/>
        <input type="date" x-model="filter.sampai" @change="loadAll()" class="form-input w-auto" placeholder="Sampai"/>
        <button @click="filter={greenhouse_id:'',dari:'',sampai:''};loadAll()" class="btn btn-secondary text-xs">Reset</button>
    </div>

    {{-- Tab switcher --}}
    <div class="flex gap-2 mb-5">
        <button @click="tab='perbandingan'"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                :style="tab==='perbandingan' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);'">
            Panen vs Terjual
        </button>
        <button @click="tab='rekap_panen'"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                :style="tab==='rekap_panen' ? 'background:var(--color-primary);color:#fff;' : 'background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);'">
            Rekap Panen per Grade
        </button>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Tab: Perbandingan --}}
    <div x-show="!loading && tab==='perbandingan'" x-cloak>
        <div class="table-wrap">
            <table class="w-full text-sm">
                <thead class="tbl-head">
                    <tr>
                        <th>Greenhouse</th>
                        <th class="text-right">Total Panen (kg)</th>
                        <th class="text-right">Total Terjual (kg)</th>
                        <th class="text-right">Sisa Stok (kg)</th>
                        <th class="text-right">Nilai Penjualan</th>
                        <th class="text-center hidden md:table-cell">Pohon Hidup</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="r in perbandingan" :key="r.greenhouse_id">
                        <tr class="tbl-row">
                            <td class="tbl-cell font-semibold" style="color:var(--color-text);" x-text="r.greenhouse"></td>
                            <td class="tbl-cell text-right" x-text="Number(r.total_panen_kg).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                            <td class="tbl-cell text-right" x-text="Number(r.total_jual_kg).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                            <td class="tbl-cell text-right font-bold"
                                :style="r.sisa_kg > 0 ? 'color:var(--color-primary)' : 'color:var(--color-text-muted)'"
                                x-text="Number(r.sisa_kg).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                            <td class="tbl-cell text-right font-bold" style="color:#059669;" x-text="'Rp ' + Number(r.total_nilai_rp).toLocaleString('id-ID')"></td>
                            <td class="tbl-cell text-center hidden md:table-cell" x-text="r.pohon_hidup"></td>
                        </tr>
                    </template>
                    <tr x-show="perbandingan.length===0">
                        <td colspan="6" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">Tidak ada data</td>
                    </tr>
                </tbody>
                <tfoot x-show="perbandingan.length > 0">
                    <tr class="tbl-row" style="background:var(--color-bg);">
                        <td class="tbl-cell font-bold" style="color:var(--color-text);">TOTAL</td>
                        <td class="tbl-cell text-right font-bold" x-text="Number(perbandingan.reduce((s,r)=>s+r.total_panen_kg,0)).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                        <td class="tbl-cell text-right font-bold" x-text="Number(perbandingan.reduce((s,r)=>s+r.total_jual_kg,0)).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                        <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);" x-text="Number(perbandingan.reduce((s,r)=>s+r.sisa_kg,0)).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                        <td class="tbl-cell text-right font-bold" style="color:#059669;" x-text="'Rp ' + Number(perbandingan.reduce((s,r)=>s+r.total_nilai_rp,0)).toLocaleString('id-ID')"></td>
                        <td class="tbl-cell hidden md:table-cell"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Tab: Rekap Panen --}}
    <div x-show="!loading && tab==='rekap_panen'" x-cloak>
        <div class="table-wrap">
            <table class="w-full text-sm">
                <thead class="tbl-head">
                    <tr>
                        <th>Greenhouse</th>
                        <th class="hidden md:table-cell">Jenis Melon</th>
                        <th class="text-center">Grade</th>
                        <th class="text-right">Total Berat (kg)</th>
                        <th class="text-right hidden md:table-cell">Jumlah Panen</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(r, idx) in rekapPanen" :key="idx">
                        <tr class="tbl-row">
                            <td class="tbl-cell text-sm" x-text="r.greenhouse"></td>
                            <td class="tbl-cell text-sm hidden md:table-cell" x-text="r.jenis_melon"></td>
                            <td class="tbl-cell text-center">
                                <span class="badge" :class="r.grade==='A'?'badge-success':'badge-neutral'" x-text="'Grade '+r.grade"></span>
                            </td>
                            <td class="tbl-cell text-right font-semibold" style="color:var(--color-primary);" x-text="Number(r.total_berat).toLocaleString('id-ID',{minimumFractionDigits:2})"></td>
                            <td class="tbl-cell text-right hidden md:table-cell" x-text="r.jumlah_panen"></td>
                        </tr>
                    </template>
                    <tr x-show="rekapPanen.length===0">
                        <td colspan="5" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">Tidak ada data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function laporanGhApp() {
    return {
        loading: false, tab: 'perbandingan',
        greenhouses: [],
        perbandingan: [], rekapPanen: [],
        filter: { greenhouse_id:'', dari:'', sampai:'' },

        async init() {
            const ghRes = await apiFetch('/api/greenhouse?semua=1');
            if (ghRes?.ok) { const d = await ghRes.json(); this.greenhouses = d.data || []; }
            await this.loadAll();
        },

        async loadAll() {
            this.loading = true;
            const qs = this.buildQs();
            const [pbRes, rpRes] = await Promise.all([
                apiFetch(`/api/laporan/greenhouse/perbandingan${qs}`),
                apiFetch(`/api/laporan/greenhouse/panen${qs}`),
            ]);
            if (pbRes?.ok) { const d = await pbRes.json(); this.perbandingan = d.data || []; }
            if (rpRes?.ok) { const d = await rpRes.json(); this.rekapPanen = d.data || []; }
            this.loading = false;
        },

        buildQs() {
            const p = new URLSearchParams();
            if (this.filter.greenhouse_id) p.set('greenhouse_id', this.filter.greenhouse_id);
            if (this.filter.dari) p.set('dari', this.filter.dari);
            if (this.filter.sampai) p.set('sampai', this.filter.sampai);
            const s = p.toString();
            return s ? '?' + s : '';
        },
    }
}
</script>
@endsection
