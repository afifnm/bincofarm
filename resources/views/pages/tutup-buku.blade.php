@extends('layouts.app')
@section('title', 'Tutup Buku')

@section('content')
<div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Tutup Buku Periode</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola penutupan dan pembukaan periode akuntansi</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openTutup()" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Tutup Periode
            </button>
            <button onclick="openBuka()" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Buka Terakhir
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div id="skeleton" class="space-y-3">
        @for($i=0;$i<3;$i++)<div class="skeleton rounded-2xl h-24"></div>@endfor
    </div>

    {{-- Periode list --}}
    <div id="periodeList" class="hidden space-y-3"></div>

    {{-- Modal Tutup Periode --}}
    <x-modal id="tutupModal" title="Tutup Periode">
        <div class="flex items-start gap-3 p-4 rounded-xl mb-5" style="background:var(--color-bg);border:1px solid var(--color-border);">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-warning);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <p class="text-xs" style="color:var(--color-text-muted);">
                Pastikan semua transaksi bulan tersebut sudah benar sebelum menutup periode. Periode yang sudah ditutup tidak bisa menerima transaksi baru.
            </p>
        </div>
        <div class="mb-5">
            <label class="form-label">Periode (YYYY-MM)</label>
            <input type="month" id="fPeriode" class="form-input"/>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal('tutupModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
            <button id="btnTutup" onclick="doTutup()" class="btn btn-primary flex-1 justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <span id="btnTutupText">Tutup Periode</span>
            </button>
        </div>
    </x-modal>

    {{-- Modal Buka Periode --}}
    <x-modal id="bukaModal" title="Buka Periode Terakhir">
        <div class="text-center py-2 mb-4">
            <div class="icon-wrap icon-wrap-amber mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </div>
            <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Buka kembali periode terakhir?</p>
            <p class="text-xs" style="color:var(--color-text-muted);">Hanya dapat dilakukan oleh administrator.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal('bukaModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
            <button id="btnBuka" onclick="doBuka()" class="btn btn-danger flex-1 justify-center">
                <span id="btnBukaText">Buka Periode</span>
            </button>
        </div>
    </x-modal>

</div>

<script>
const app = { items: [], loading: true, saving: false };

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatRp(n) {
    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
}

const LOCK_CLOSED_SVG = `<svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>`;
const LOCK_OPEN_SVG = `<svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>`;

function render() {
    const wrap = document.getElementById('periodeList');
    if (!app.items.length) {
        wrap.innerHTML = `
            <div class="text-center py-16" style="color:var(--color-text-muted);">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                <p class="text-sm font-medium">Belum ada periode</p>
                <p class="text-xs mt-1">Lakukan transaksi terlebih dahulu untuk membuat periode</p>
            </div>`;
        return;
    }
    wrap.innerHTML = app.items.map(periode => {
        const closedAt = periode.closed_at ? new Date(periode.closed_at).toLocaleDateString('id-ID') : '';
        const kasRows = (periode.kas || []).map(k => `
            <tr class="tbl-row">
                <td class="tbl-cell">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full shrink-0" style="${k.is_closed ? 'background:var(--color-success);' : 'background:var(--color-warning);'}"></div>
                        <span style="color:var(--color-text-muted);">${escHtml(k.kas_nama)}</span>
                    </div>
                </td>
                <td class="tbl-cell text-right font-semibold" style="color:var(--color-text);">${formatRp(k.saldo_akhir)}</td>
                <td class="tbl-cell text-right w-20">
                    <span class="badge ${k.is_closed ? 'badge-success' : 'badge-warning'}">${k.is_closed ? 'Ditutup' : 'Terbuka'}</span>
                </td>
            </tr>
        `).join('');
        return `
            <div class="table-wrap overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3.5 border-b" style="border-color:var(--color-border);${periode.is_closed ? 'background:var(--color-primary-soft);' : 'background:var(--color-surface);'}">
                    <div class="flex items-center gap-3">
                        <div class="icon-wrap shrink-0 ${periode.is_closed ? 'icon-wrap-emerald' : 'icon-wrap-amber'}" style="width:36px;height:36px;border-radius:10px;">
                            ${periode.is_closed ? LOCK_CLOSED_SVG : LOCK_OPEN_SVG}
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color:var(--color-text);">${escHtml(periode.periode)}</p>
                            <p class="text-xs" style="color:var(--color-text-muted);">
                                ${periode.is_closed ? 'Ditutup ' + closedAt : 'Periode masih terbuka'}
                            </p>
                        </div>
                    </div>
                    <span class="badge ${periode.is_closed ? 'badge-success' : 'badge-warning'}">${periode.is_closed ? 'Ditutup' : 'Terbuka'}</span>
                </div>
                <table class="w-full text-xs">
                    <tbody>${kasRows}</tbody>
                </table>
            </div>`;
    }).join('');
}

async function load() {
    document.getElementById('skeleton').classList.remove('hidden');
    document.getElementById('periodeList').classList.add('hidden');
    const res = await apiFetch('/api/periode');
    if (res?.ok) app.items = await res.json();
    document.getElementById('skeleton').classList.add('hidden');
    document.getElementById('periodeList').classList.remove('hidden');
    render();
}

function openTutup() {
    document.getElementById('fPeriode').value = new Date().toISOString().slice(0, 7);
    openModal('tutupModal');
}
function openBuka() {
    openModal('bukaModal');
}

async function doTutup() {
    if (app.saving) return;
    app.saving = true;
    const btn = document.getElementById('btnTutup');
    btn.disabled = true;
    document.getElementById('btnTutupText').textContent = 'Memproses...';
    const res = await apiFetch('/api/periode/tutup', { method:'POST', body: JSON.stringify({ periode: document.getElementById('fPeriode').value }) });
    const data = await res.json();
    if (res.ok) {
        toast('Periode ditutup.', 'success');
        closeModal('tutupModal');
        await load();
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false;
    document.getElementById('btnTutupText').textContent = 'Tutup Periode';
    app.saving = false;
}

async function doBuka() {
    if (app.saving) return;
    app.saving = true;
    const btn = document.getElementById('btnBuka');
    btn.disabled = true;
    document.getElementById('btnBukaText').textContent = 'Memproses...';
    const res = await apiFetch('/api/periode/buka', { method:'POST' });
    const data = await res.json();
    if (res.ok) {
        toast('Periode dibuka.', 'success');
        closeModal('bukaModal');
        await load();
    } else {
        toast(data.message || 'Gagal.', 'error');
    }
    btn.disabled = false;
    document.getElementById('btnBukaText').textContent = 'Buka Periode';
    app.saving = false;
}

document.addEventListener('DOMContentLoaded', load);
</script>
@endsection
