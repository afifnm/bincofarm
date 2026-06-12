@extends('layouts.app')
@section('title', 'Greenhouse — Laporan')

@section('breadcrumb')
    <span>Greenhouse</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Laporan</span>
@endsection

@section('content')
<div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Laporan Greenhouse</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Rekap panen, penjualan, dan perbandingan stok</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mb-5 flex flex-wrap items-center gap-3">
        <select id="filterGh" onchange="app.filter.greenhouse_id=this.value;loadAll()" class="form-input w-auto min-w-[150px]">
            <option value="">Semua GH</option>
        </select>
        <input type="date" id="filterDari" onchange="app.filter.dari=this.value;loadAll()" class="form-input w-auto" placeholder="Dari"/>
        <input type="date" id="filterSampai" onchange="app.filter.sampai=this.value;loadAll()" class="form-input w-auto" placeholder="Sampai"/>
        <button onclick="resetFilters()" class="btn btn-secondary text-xs">Reset</button>
    </div>

    {{-- Tab switcher --}}
    <div class="flex gap-2 mb-5">
        <button id="tabBtnPerbandingan" onclick="setTab('perbandingan')"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                style="background:var(--color-primary);color:#fff;">
            Panen vs Terjual
        </button>
        <button id="tabBtnRekap" onclick="setTab('rekap_panen')"
                class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);">
            Rekap Panen per Grade
        </button>
    </div>

    {{-- Loading --}}
    <div id="skeleton" class="hidden space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Tab: Perbandingan --}}
    <div id="tabPerbandingan" class="table-wrap">
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
            <tbody id="tblPerbandingan"></tbody>
            <tfoot id="tfootPerbandingan" class="hidden">
                <tr class="tbl-row" style="background:var(--color-bg);">
                    <td class="tbl-cell font-bold" style="color:var(--color-text);">TOTAL</td>
                    <td class="tbl-cell text-right font-bold" id="totPanen"></td>
                    <td class="tbl-cell text-right font-bold" id="totJual"></td>
                    <td class="tbl-cell text-right font-bold" id="totSisa" style="color:var(--color-primary);"></td>
                    <td class="tbl-cell text-right font-bold" id="totNilai" style="color:#059669;"></td>
                    <td class="tbl-cell hidden md:table-cell"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Tab: Rekap Panen --}}
    <div id="tabRekapPanen" class="hidden table-wrap">
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
            <tbody id="tblRekap"></tbody>
        </table>
    </div>

</div>

<script>
const app = {
    loading: false, tab: 'perbandingan',
    greenhouses: [],
    perbandingan: [], rekapPanen: [],
    filter: { greenhouse_id:'', dari:'', sampai:'' }
};

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtKg(n) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits:2, maximumFractionDigits:2 });
}
function fmtRp(n) {
    return Number(n || 0).toLocaleString('id-ID');
}

function setTab(tab) {
    app.tab = tab;
    const activeStyle = 'background:var(--color-primary);color:#fff;border:none;';
    const inactiveStyle = 'background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);';
    document.getElementById('tabBtnPerbandingan').style.cssText = tab === 'perbandingan' ? activeStyle : inactiveStyle;
    document.getElementById('tabBtnRekap').style.cssText = tab === 'rekap_panen' ? activeStyle : inactiveStyle;
    document.getElementById('tabPerbandingan').classList.toggle('hidden', tab !== 'perbandingan');
    document.getElementById('tabRekapPanen').classList.toggle('hidden', tab !== 'rekap_panen');
}

function renderPerbandingan() {
    const tbody = document.getElementById('tblPerbandingan');
    if (!app.perbandingan.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">Tidak ada data</td></tr>`;
        document.getElementById('tfootPerbandingan').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.perbandingan.map(r => `
        <tr class="tbl-row">
            <td class="tbl-cell font-semibold" style="color:var(--color-text);">${escHtml(r.greenhouse)}</td>
            <td class="tbl-cell text-right">${fmtKg(r.total_panen_kg)}</td>
            <td class="tbl-cell text-right">${fmtKg(r.total_jual_kg)}</td>
            <td class="tbl-cell text-right font-bold" style="${r.sisa_kg > 0 ? 'color:var(--color-primary)' : 'color:var(--color-text-muted)'}">${fmtKg(r.sisa_kg)}</td>
            <td class="tbl-cell text-right font-bold" style="color:#059669;">Rp ${fmtRp(r.total_nilai_rp)}</td>
            <td class="tbl-cell text-center hidden md:table-cell">${escHtml(r.pohon_hidup)}</td>
        </tr>
    `).join('');
    const totPanen = app.perbandingan.reduce((s,r) => s + Number(r.total_panen_kg), 0);
    const totJual  = app.perbandingan.reduce((s,r) => s + Number(r.total_jual_kg), 0);
    const totSisa  = app.perbandingan.reduce((s,r) => s + Number(r.sisa_kg), 0);
    const totNilai = app.perbandingan.reduce((s,r) => s + Number(r.total_nilai_rp), 0);
    document.getElementById('totPanen').textContent = fmtKg(totPanen);
    document.getElementById('totJual').textContent  = fmtKg(totJual);
    document.getElementById('totSisa').textContent  = fmtKg(totSisa);
    document.getElementById('totNilai').textContent = 'Rp ' + fmtRp(totNilai);
    document.getElementById('tfootPerbandingan').classList.remove('hidden');
}

function renderRekap() {
    const tbody = document.getElementById('tblRekap');
    if (!app.rekapPanen.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="tbl-cell text-center py-10" style="color:var(--color-text-muted);">Tidak ada data</td></tr>`;
        return;
    }
    tbody.innerHTML = app.rekapPanen.map(r => `
        <tr class="tbl-row">
            <td class="tbl-cell text-sm">${escHtml(r.greenhouse)}</td>
            <td class="tbl-cell text-sm hidden md:table-cell">${escHtml(r.jenis_melon)}</td>
            <td class="tbl-cell text-center">
                <span class="badge ${r.grade === 'A' ? 'badge-success' : 'badge-neutral'}">Grade ${escHtml(r.grade)}</span>
            </td>
            <td class="tbl-cell text-right font-semibold" style="color:var(--color-primary);">${fmtKg(r.total_berat)}</td>
            <td class="tbl-cell text-right hidden md:table-cell">${escHtml(r.jumlah_panen)}</td>
        </tr>
    `).join('');
}

function buildQs() {
    const p = new URLSearchParams();
    if (app.filter.greenhouse_id) p.set('greenhouse_id', app.filter.greenhouse_id);
    if (app.filter.dari) p.set('dari', app.filter.dari);
    if (app.filter.sampai) p.set('sampai', app.filter.sampai);
    const s = p.toString();
    return s ? '?' + s : '';
}

async function loadAll() {
    document.getElementById('skeleton').classList.remove('hidden');
    document.getElementById('tabPerbandingan').classList.add('hidden');
    document.getElementById('tabRekapPanen').classList.add('hidden');
    const qs = buildQs();
    const [pbRes, rpRes] = await Promise.all([
        apiFetch(`/api/laporan/greenhouse/perbandingan${qs}`),
        apiFetch(`/api/laporan/greenhouse/panen${qs}`),
    ]);
    if (pbRes?.ok) { const d = await pbRes.json(); app.perbandingan = d.data || []; }
    if (rpRes?.ok) { const d = await rpRes.json(); app.rekapPanen = d.data || []; }
    document.getElementById('skeleton').classList.add('hidden');
    renderPerbandingan();
    renderRekap();
    setTab(app.tab);
}

function resetFilters() {
    app.filter = { greenhouse_id:'', dari:'', sampai:'' };
    ['filterGh','filterDari','filterSampai'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    loadAll();
}

document.addEventListener('DOMContentLoaded', async () => {
    const ghRes = await apiFetch('/api/greenhouse?semua=1');
    if (ghRes?.ok) {
        const d = await ghRes.json();
        app.greenhouses = d.data || [];
        const sel = document.getElementById('filterGh');
        app.greenhouses.forEach(gh => sel.appendChild(new Option(gh.nama, gh.id)));
    }
    await loadAll();
});
</script>
@endsection
