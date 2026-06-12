@extends('layouts.app')
@section('title', 'Greenhouse — Penjualan Melon')

@section('breadcrumb')
    <span>Greenhouse</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Penjualan Melon</span>
@endsection

@push('styles')
<style>
    #nota-print { display: none; }
    @media print {
        body * { visibility: hidden; }
        #nota-print, #nota-print * { visibility: visible; }
        #nota-print {
            display: block !important;
            position: absolute; left: 0; top: 0;
            width: 58mm; padding: 2mm;
            background: #fff; color: #000;
        }
        #nota-print pre {
            font-family: 'Courier New', monospace;
            font-size: 9pt; line-height: 1.25;
            white-space: pre; margin: 0;
        }
        @page { size: 58mm auto; margin: 0; }
    }
</style>
@endpush

@section('content')
<div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Penjualan Melon</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Satu nota bisa berisi beberapa jenis melon. Kas masuk tercatat otomatis</p>
        </div>
        <button id="btnAddPenjualan" onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Penjualan
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="mb-4 flex flex-wrap items-center gap-2 md:gap-3">
        <div id="normalFilters" class="flex flex-wrap items-center gap-2 md:gap-3 flex-1">
            <div class="relative flex-1 min-w-[150px] md:flex-none md:w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="text" id="filterSearch" oninput="searchDebounce(this.value)" placeholder="Cari pembeli / no nota..."
                       class="form-input pl-9 w-full"/>
            </div>
            <select id="filterGh" onchange="app.filter.greenhouse_id=this.value;load()" class="form-input w-auto min-w-[130px]">
                <option value="">Semua GH</option>
            </select>
            <input type="date" id="filterDari" onchange="app.filter.dari=this.value;load()" class="form-input w-auto" placeholder="Dari"/>
            <input type="date" id="filterSampai" onchange="app.filter.sampai=this.value;load()" class="form-input w-auto" placeholder="Sampai"/>
            <button onclick="resetFilters()" class="btn btn-secondary text-xs">Reset</button>
        </div>
        <button id="btnVoid" type="button" onclick="toggleVoidFilter()"
                class="filter-pill text-xs px-3 py-1.5 flex items-center gap-1.5">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            Void
        </button>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Desktop table --}}
    <div id="desktopWrap" class="hidden table-wrap hidden md:block">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th>No Nota</th>
                    <th>Tanggal</th>
                    <th>Greenhouse</th>
                    <th>Pembeli</th>
                    <th class="hidden lg:table-cell">Item</th>
                    <th class="text-right hidden lg:table-cell">Total Kg</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="desktopTbl"></tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div id="mobileWrap" class="hidden md:hidden">
        <div id="mobileCards" class="space-y-3"></div>
    </div>

    {{-- Pagination --}}
    <div id="pageBar" class="hidden flex items-center justify-between mt-4 px-1">
        <p class="text-xs" id="pageInfo" style="color:var(--color-text-muted);"></p>
        <div class="flex gap-1">
            <button id="btnPrev" onclick="load(app.meta.current_page-1)" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);background:var(--color-surface);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button id="btnNext" onclick="load(app.meta.current_page+1)" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);background:var(--color-surface);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- Modal Form (multi-item) --}}
    <div id="formModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('formModal')"></div>
        <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 id="formTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah Penjualan</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="penjualanForm" onsubmit="save(event)" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Greenhouse</label>
                            <select id="fGhId" required class="form-input">
                                <option value="">— Pilih GH —</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tanggal</label>
                            <input type="date" id="fTanggal" required class="form-input"/>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Nama Pembeli</label>
                        <input type="text" id="fPembeli" required class="form-input" placeholder="Nama pembeli"/>
                    </div>

                    {{-- Item rows --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="form-label mb-0">Item Melon</label>
                            <button type="button" onclick="addItem()" class="btn btn-secondary text-xs py-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Item
                            </button>
                        </div>
                        <div class="hidden sm:grid grid-cols-12 gap-2 px-3 py-2 text-xs font-semibold" style="color:var(--color-text-muted);">
                            <div class="col-span-5">Jenis Melon</div>
                            <div class="col-span-2">Jumlah (kg)</div>
                            <div class="col-span-3">Harga/kg (Rp)</div>
                            <div class="col-span-2 text-right">Aksi</div>
                        </div>
                        <div id="formItemsContainer" class="space-y-2"></div>
                    </div>

                    <div class="rounded-xl px-4 py-3 flex items-center justify-between" style="background:var(--color-primary-soft);">
                        <div>
                            <p class="text-xs" style="color:var(--color-text-muted);">Total (<span id="totalKgForm">0,00 kg</span>)</p>
                            <p class="text-lg font-bold" id="totalForm" style="color:var(--color-primary);">Rp 0</p>
                        </div>
                        <p class="text-xs" id="itemCount" style="color:var(--color-text-muted);">0 item</p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn btn-primary flex-1 justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Nota --}}
    <div id="notaModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('notaModal')"></div>
        <div class="relative w-full max-w-sm rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Nota Penjualan</h3>
                <button type="button" onclick="closeModal('notaModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="rounded-xl p-3 mb-4 overflow-x-auto" style="background:#fff;border:1px solid var(--color-border);">
                    <pre id="notaPreview" class="mx-auto" style="font-family:'Courier New',monospace;font-size:11px;line-height:1.3;color:#000;width:max-content;"></pre>
                </div>
                <div class="space-y-2">
                    <button id="btnBtPrint" onclick="printBluetooth()" class="btn btn-primary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.25 6.087l5.25 4.5-7.5 6.426V3.087l7.5 6.426-5.25 4.5M4.5 8.25l4.875 4.125L4.5 16.5"/></svg>
                        <span id="btPrintLabel">Print Thermal Bluetooth</span>
                    </button>
                    <button onclick="printBrowser()" class="btn btn-secondary w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                        Print via Browser
                    </button>
                    <button onclick="closeModal('notaModal')" class="btn btn-secondary w-full justify-center" style="border:none;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('deleteModal')"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="px-5 py-5">
                <p class="text-sm font-medium mb-1 text-center" style="color:var(--color-text);">
                    Hapus nota <span class="font-mono" id="deleteNotaNo"></span>?
                </p>
                <p class="text-xs mb-5 text-center" style="color:var(--color-text-muted);">Transaksi kas terkait akan di-void secara otomatis.</p>
                <div class="flex gap-3">
                    <button onclick="closeModal('deleteModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button id="btnDelete" onclick="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Area cetak nota (hidden, shown only on print) --}}
<div id="nota-print"><pre id="nota-print-text"></pre></div>

<script>
/* ═══════════ ESC/POS + Web Bluetooth thermal printer ═══════════ */
const ThermalPrinter = {
    COLS: 32,
    device: null,
    characteristic: null,

    SERVICES: [
        '000018f0-0000-1000-8000-00805f9b34fb',
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
    ],

    get connected() {
        return !!(this.characteristic && this.device?.gatt?.connected);
    },

    async connect() {
        if (this.connected) return;
        if (!navigator.bluetooth) {
            throw new Error('Browser tidak mendukung Web Bluetooth. Gunakan Chrome di Android, dan akses lewat HTTPS.');
        }
        const device = await navigator.bluetooth.requestDevice({
            filters: this.SERVICES.map(s => ({ services: [s] })),
            optionalServices: this.SERVICES,
        });
        device.addEventListener('gattserverdisconnected', () => { this.characteristic = null; });
        const server = await device.gatt.connect();
        for (const uuid of this.SERVICES) {
            try {
                const service = await server.getPrimaryService(uuid);
                const chars   = await service.getCharacteristics();
                const ch      = chars.find(c => c.properties.writeWithoutResponse || c.properties.write);
                if (ch) { this.device = device; this.characteristic = ch; return; }
            } catch (e) { /* try next */ }
        }
        device.gatt.disconnect();
        throw new Error('Printer tersambung tapi karakteristik tulis tidak ditemukan.');
    },

    async write(bytes) {
        await this.connect();
        const CHUNK = 100;
        for (let i = 0; i < bytes.length; i += CHUNK) {
            const slice = bytes.slice(i, i + CHUNK);
            if (this.characteristic.properties.writeWithoutResponse) {
                await this.characteristic.writeValueWithoutResponse(slice);
            } else {
                await this.characteristic.writeValue(slice);
            }
            await new Promise(r => setTimeout(r, 25));
        }
    },

    buildEscPos(lines) {
        const bytes = [0x1B, 0x40];
        const push  = (...b) => bytes.push(...b);
        const pushText = (t) => {
            for (const c of t) {
                const code = c.charCodeAt(0);
                push(code >= 32 && code <= 126 ? code : 0x20);
            }
        };
        for (const line of lines) {
            push(0x1B, 0x61, line.align === 'center' ? 1 : 0);
            push(0x1B, 0x45, line.bold ? 1 : 0);
            push(0x1D, 0x21, line.big ? 0x11 : 0x00);
            pushText(line.text);
            push(0x0A);
        }
        push(0x1D, 0x21, 0x00, 0x1B, 0x45, 0x00, 0x1B, 0x61, 0x00);
        push(0x0A, 0x0A, 0x0A, 0x0A);
        return new Uint8Array(bytes);
    },
};
/* ═══════════ end ThermalPrinter ═══════════ */
</script>

<script>
const app = {
    items: [], loading: true, editId: null, deleteTarget: null,
    saving: false, deleting: false, btPrinting: false,
    greenhouses: [], jenisList: [],
    notaTarget: null, notaText: '',
    filterVoid: false,
    filter: { search:'', greenhouse_id:'', dari:'', sampai:'' },
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
    formItems: [],
};

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtRp(n) { return Number(n || 0).toLocaleString('id-ID'); }
function fmtKg(n) { return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits:2, maximumFractionDigits:2 }); }
function subtotalItem(item) { return (parseFloat(item.jumlah_kg) || 0) * (parseFloat(item.harga_per_kg) || 0); }

function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('desktopWrap').classList.toggle('hidden', v);
    document.getElementById('mobileWrap').classList.toggle('hidden', v);
}

function render() {
    const pj = app.items;

    // Desktop tbody
    const dt = document.getElementById('desktopTbl');
    if (!pj.length) {
        dt.innerHTML = `<tr><td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);"><p class="text-sm font-medium">Belum ada data penjualan</p></td></tr>`;
    } else {
        dt.innerHTML = pj.map(p => {
            const itemsText = (p.items || []).map(i => `${escHtml(i.jenis_melon?.nama || '?')} ${fmtKg(i.jumlah_kg)}kg`).join(', ');
            const actionOrVoid = p.deleted_at
                ? `<span class="badge badge-danger mx-auto block w-fit">VOID</span>`
                : `<div class="flex items-center justify-center gap-1">
                    <button onclick="openNotaById(${p.id})" title="Cetak Nota" class="w-8 h-8 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='#059669'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                    </button>
                    <button onclick="openEditById(${p.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                    </button>
                    <button onclick="openDeleteById(${p.id})" title="Hapus" class="w-8 h-8 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                   </div>`;
            return `<tr class="tbl-row${p.deleted_at ? ' opacity-60' : ''}">
                <td class="tbl-cell text-xs font-mono font-semibold" style="color:var(--color-primary);">${escHtml(p.no_nota || '—')}</td>
                <td class="tbl-cell text-sm">${escHtml(formatDate(p.tanggal))}</td>
                <td class="tbl-cell text-sm">${escHtml(p.greenhouse?.nama || '—')}</td>
                <td class="tbl-cell text-sm font-medium">${escHtml(p.nama_pembeli)}</td>
                <td class="tbl-cell text-xs hidden lg:table-cell" style="color:var(--color-text-muted);">${itemsText}</td>
                <td class="tbl-cell text-right hidden lg:table-cell">${fmtKg(p.total_kg)} kg</td>
                <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);">Rp ${fmtRp(p.total)}</td>
                <td class="tbl-cell">${actionOrVoid}</td>
            </tr>`;
        }).join('');
    }

    // Mobile cards
    const mc = document.getElementById('mobileCards');
    if (!pj.length) {
        mc.innerHTML = `<div class="rounded-2xl py-12 text-center" style="background:var(--color-surface);border:1px solid var(--color-border);color:var(--color-text-muted);"><p class="text-sm font-medium">Belum ada data penjualan</p></div>`;
    } else {
        mc.innerHTML = pj.map(p => {
            const itemRows = (p.items || []).map(it => `
                <div class="flex justify-between text-xs">
                    <span style="color:var(--color-text);">${escHtml(it.jenis_melon?.nama || '?')} — ${fmtKg(it.jumlah_kg)} kg × Rp ${fmtRp(it.harga_per_kg)}</span>
                    <span class="font-medium shrink-0 pl-2" style="color:var(--color-text);">Rp ${fmtRp(it.subtotal)}</span>
                </div>`).join('');
            const actionBtns = p.deleted_at ? '' : `
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="openNotaById(${p.id})" class="btn btn-secondary justify-center text-xs py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                        Nota
                    </button>
                    <button onclick="openEditById(${p.id})" class="btn btn-secondary justify-center text-xs py-2.5">Edit</button>
                    <button onclick="openDeleteById(${p.id})" class="btn btn-secondary justify-center text-xs py-2.5" style="color:#B91C1C;">Hapus</button>
                </div>`;
            return `
                <div class="rounded-2xl p-4" style="${p.deleted_at ? 'background:var(--color-surface);border:1px solid #FECACA;opacity:.8;' : 'background:var(--color-surface);border:1px solid var(--color-border);'}">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-mono font-semibold" style="color:var(--color-primary);">${escHtml(p.no_nota || '—')}</p>
                                ${p.deleted_at ? '<span class="badge badge-danger text-xs">VOID</span>' : ''}
                            </div>
                            <p class="text-sm font-semibold mt-0.5 truncate" style="color:var(--color-text);">${escHtml(p.nama_pembeli)}</p>
                            <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(formatDate(p.tanggal))} · ${escHtml(p.greenhouse?.nama || '—')}</p>
                        </div>
                        <p class="text-base font-bold shrink-0" style="color:var(--color-primary);">Rp ${fmtRp(p.total)}</p>
                    </div>
                    <div class="rounded-xl px-3 py-2 mb-3 space-y-1" style="background:var(--color-bg);">${itemRows}</div>
                    ${actionBtns}
                </div>`;
        }).join('');
    }

    // Pagination
    const bar = document.getElementById('pageBar');
    if (app.meta.last_page > 1) {
        bar.classList.remove('hidden');
        document.getElementById('pageInfo').textContent = `${app.meta.from}–${app.meta.to} dari ${app.meta.total}`;
        document.getElementById('btnPrev').disabled = app.meta.current_page <= 1;
        document.getElementById('btnNext').disabled = app.meta.current_page >= app.meta.last_page;
    } else {
        bar.classList.add('hidden');
    }
}

async function load(page = 1) {
    setLoading(true);
    let url = `/api/penjualan-melon?page=${page}&per_page=20`;
    if (app.filterVoid) {
        url += `&hanya_void=1`;
    } else {
        if (app.filter.search)        url += `&search=${encodeURIComponent(app.filter.search)}`;
        if (app.filter.greenhouse_id) url += `&greenhouse_id=${app.filter.greenhouse_id}`;
        if (app.filter.dari)          url += `&dari=${app.filter.dari}`;
        if (app.filter.sampai)        url += `&sampai=${app.filter.sampai}`;
    }
    const res = await apiFetch(url);
    if (res?.ok) {
        const d = await res.json();
        app.items = d.data || [];
        app.meta  = d.meta  || { current_page: d.current_page, last_page: d.last_page, from: d.from, to: d.to, total: d.total };
    }
    setLoading(false);
    render();
}

function resetFilters() {
    app.filter = { search:'', greenhouse_id:'', dari:'', sampai:'' };
    ['filterSearch','filterGh','filterDari','filterSampai'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    load();
}

function toggleVoidFilter() {
    app.filterVoid = !app.filterVoid;
    document.getElementById('normalFilters').classList.toggle('hidden', app.filterVoid);
    document.getElementById('btnAddPenjualan').classList.toggle('hidden', app.filterVoid);
    const btn = document.getElementById('btnVoid');
    if (app.filterVoid) {
        btn.style.cssText = 'background:#FEE2E2;color:#B91C1C;border-color:#FECACA;font-weight:600;';
    } else {
        btn.style.cssText = '';
    }
    load();
}

/* ── Form item helpers ── */
function renderFormItems() {
    const cont = document.getElementById('formItemsContainer');
    cont.innerHTML = app.formItems.map((item, idx) => {
        const selected = String(item.jenis_melon_id || '');
        const opts = app.jenisList.map(jm =>
            `<option value="${jm.id}" ${String(jm.id) === selected ? 'selected' : ''}>${escHtml(jm.nama)}</option>`
        ).join('');
        const sub = subtotalItem(item);
        return `
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 rounded-xl p-3" style="background:var(--color-bg);border:1px solid var(--color-border);">
                <div class="sm:col-span-5">
                    <label class="text-xs sm:hidden font-semibold mb-1 block" style="color:var(--color-text-muted);">Jenis Melon</label>
                    <select onchange="app.formItems[${idx}].jenis_melon_id=this.value" required class="form-input w-full">
                        <option value="">— Pilih Melon —</option>
                        ${opts}
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs sm:hidden font-semibold mb-1 block" style="color:var(--color-text-muted);">Jumlah (kg)</label>
                    <input type="number" value="${escHtml(item.jumlah_kg)}" oninput="app.formItems[${idx}].jumlah_kg=this.value;updateTotals()" min="0.01" step="0.01" required class="form-input w-full" placeholder="0.00"/>
                </div>
                <div class="sm:col-span-3">
                    <label class="text-xs sm:hidden font-semibold mb-1 block" style="color:var(--color-text-muted);">Harga/kg (Rp)</label>
                    <input type="number" value="${escHtml(item.harga_per_kg)}" oninput="app.formItems[${idx}].harga_per_kg=this.value;updateTotals()" min="0" step="1" required class="form-input w-full" placeholder="0"/>
                </div>
                <div class="sm:col-span-2 flex items-center justify-between gap-2 pt-1 sm:pt-0">
                    <p class="text-xs font-medium sm:hidden" id="subtotal-mob-${idx}" style="color:var(--color-primary);">Rp ${fmtRp(sub)}</p>
                    <button type="button" onclick="removeItem(${idx})" ${app.formItems.length === 1 ? 'disabled' : ''}
                            class="ml-auto w-9 h-9 flex items-center justify-center rounded-lg disabled:opacity-30"
                            style="color:#B91C1C;border:1px solid var(--color-border);" title="Hapus item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="col-span-1 sm:col-span-12 hidden sm:flex items-center justify-end">
                    <p class="text-xs font-medium" id="subtotal-desk-${idx}" style="color:var(--color-primary);">Subtotal: Rp ${fmtRp(sub)}</p>
                </div>
            </div>`;
    }).join('');
    updateTotals();
}

function updateTotals() {
    let total = 0, totalKg = 0;
    app.formItems.forEach((item, idx) => {
        const sub = subtotalItem(item);
        total   += sub;
        totalKg += parseFloat(item.jumlah_kg) || 0;
        const mob  = document.getElementById(`subtotal-mob-${idx}`);
        const desk = document.getElementById(`subtotal-desk-${idx}`);
        if (mob)  mob.textContent  = 'Rp ' + fmtRp(sub);
        if (desk) desk.textContent = 'Subtotal: Rp ' + fmtRp(sub);
    });
    document.getElementById('totalForm').textContent   = 'Rp ' + fmtRp(total);
    document.getElementById('totalKgForm').textContent = fmtKg(totalKg) + ' kg';
    document.getElementById('itemCount').textContent   = app.formItems.length + ' item';
}

function addItem() {
    app.formItems.push({ jenis_melon_id:'', jumlah_kg:'', harga_per_kg:'' });
    renderFormItems();
}
function removeItem(idx) {
    if (app.formItems.length > 1) { app.formItems.splice(idx, 1); renderFormItems(); }
}

function openCreate() {
    app.editId = null;
    document.getElementById('formTitle').textContent = 'Tambah Penjualan';
    document.getElementById('fGhId').disabled = false;
    document.getElementById('fGhId').value    = app.greenhouses.length === 1 ? String(app.greenhouses[0].id) : '';
    document.getElementById('fTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('fPembeli').value = '';
    app.formItems = [{ jenis_melon_id:'', jumlah_kg:'', harga_per_kg:'' }];
    renderFormItems();
    openModal('formModal');
}

function openEditById(id) {
    const pj = app.items.find(x => x.id === id);
    if (!pj) return;
    app.editId = id;
    document.getElementById('formTitle').textContent = 'Edit Penjualan';
    document.getElementById('fGhId').disabled = true;
    document.getElementById('fGhId').value    = String(pj.greenhouse_id);
    document.getElementById('fTanggal').value = pj.tanggal;
    document.getElementById('fPembeli').value = pj.nama_pembeli;
    app.formItems = (pj.items || []).map(i => ({
        jenis_melon_id: String(i.jenis_melon_id),
        jumlah_kg:      String(i.jumlah_kg),
        harga_per_kg:   String(i.harga_per_kg),
    }));
    if (!app.formItems.length) app.formItems = [{ jenis_melon_id:'', jumlah_kg:'', harga_per_kg:'' }];
    renderFormItems();
    openModal('formModal');
}

function openDeleteById(id) {
    app.deleteTarget = app.items.find(x => x.id === id);
    document.getElementById('deleteNotaNo').textContent = app.deleteTarget?.no_nota || '';
    openModal('deleteModal');
}

async function save(e) {
    e.preventDefault();
    if (app.saving) return;
    app.saving = true;
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const form = {
        greenhouse_id: document.getElementById('fGhId').value,
        nama_pembeli:  document.getElementById('fPembeli').value,
        tanggal:       document.getElementById('fTanggal').value,
        items:         app.formItems,
    };
    const url    = app.editId ? `/api/penjualan-melon/${app.editId}` : '/api/penjualan-melon';
    const method = app.editId ? 'PUT' : 'POST';
    const res    = await apiFetch(url, { method, body: JSON.stringify(form) });
    const data   = await res.json();
    if (res.ok) {
        toast(app.editId ? 'Penjualan diperbarui.' : 'Penjualan disimpan & kas diperbarui.', 'success');
        const wasNew  = !app.editId;
        const savedId = data?.id;
        closeModal('formModal');
        await load(app.meta.current_page);
        if (wasNew && savedId) {
            const saved = app.items.find(i => i.id === savedId) || data;
            openNotaById(savedId, saved);
        }
    } else {
        toast(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Gagal menyimpan.'), 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
    app.saving = false;
}

async function doDelete() {
    if (app.deleting || !app.deleteTarget) return;
    app.deleting = true;
    const btn = document.getElementById('btnDelete');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    try {
        const res  = await apiFetch(`/api/penjualan-melon/${app.deleteTarget.id}`, { method: 'DELETE' });
        const data = await res.json();
        if (res.ok) {
            toast('Penjualan dihapus & transaksi kas di-void.', 'success');
            closeModal('deleteModal');
            await load(app.meta.current_page);
        } else {
            toast(data.message || 'Gagal menghapus.', 'error');
        }
    } finally {
        btn.disabled = false; btn.textContent = 'Hapus';
        app.deleting = false;
    }
}

/* ── Nota helpers ── */
function notaLines(pj) {
    const C = ThermalPrinter.COLS;
    const kv = (k, v) => (k + ': ').padEnd(9) + String(v ?? '—');
    const lr = (l, r) => { l = String(l); r = String(r); const pad = C - l.length - r.length; return pad > 0 ? l + ' '.repeat(pad) + r : l + ' ' + r; };
    const tglParts = (pj.tanggal || '').split('-');
    const tgl = tglParts.length === 3 ? `${tglParts[2]}/${tglParts[1]}/${tglParts[0]}` : pj.tanggal;
    const lines = [
        { text: 'BINCO INDOFARM', align: 'center', bold: true, big: true },
        { text: pj.greenhouse?.nama || '-', align: 'center', bold: true },
    ];
    if (pj.greenhouse?.lokasi) lines.push({ text: pj.greenhouse.lokasi, align: 'center' });
    lines.push({ text: '='.repeat(C) });
    lines.push({ text: kv('No', pj.no_nota) });
    lines.push({ text: kv('Tgl', tgl) });
    lines.push({ text: kv('Pembeli', pj.nama_pembeli) });
    lines.push({ text: '-'.repeat(C) });
    for (const it of (pj.items || [])) {
        lines.push({ text: it.jenis_melon?.nama || 'Melon' });
        lines.push({ text: lr(`  ${fmtKg(it.jumlah_kg)}kg x ${fmtRp(it.harga_per_kg)}`, fmtRp(it.subtotal)) });
    }
    lines.push({ text: '-'.repeat(C) });
    lines.push({ text: lr('TOTAL KG', fmtKg(pj.total_kg) + ' kg') });
    lines.push({ text: lr('TOTAL', 'Rp ' + fmtRp(pj.total)), bold: true });
    lines.push({ text: '='.repeat(C) });
    lines.push({ text: 'Terima kasih atas', align: 'center' });
    lines.push({ text: 'pembelian Anda!', align: 'center' });
    return lines;
}

function notaToText(lines) {
    const C = ThermalPrinter.COLS;
    return lines.map(l => {
        if (l.align === 'center') {
            const w   = l.big ? Math.floor(C / 2) : C;
            const pad = Math.max(0, Math.floor((w - l.text.length) / 2));
            return ' '.repeat(pad) + l.text;
        }
        return l.text;
    }).join('\n');
}

function openNotaById(id, pjObj) {
    const pj = pjObj || app.items.find(x => x.id === id);
    if (!pj) return;
    app.notaTarget = pj;
    app.notaText   = notaToText(notaLines(pj));
    document.getElementById('notaPreview').textContent   = app.notaText;
    document.getElementById('btPrintLabel').textContent  = ThermalPrinter.connected
        ? 'Print Thermal (Tersambung)' : 'Print Thermal Bluetooth';
    openModal('notaModal');
}

function printBrowser() {
    document.getElementById('nota-print-text').textContent = app.notaText;
    window.print();
}

async function printBluetooth() {
    if (app.btPrinting) return;
    app.btPrinting = true;
    const btn = document.getElementById('btnBtPrint');
    btn.disabled = true;
    document.getElementById('btPrintLabel').textContent = 'Mencetak...';
    try {
        await ThermalPrinter.write(ThermalPrinter.buildEscPos(notaLines(app.notaTarget)));
        document.getElementById('btPrintLabel').textContent = 'Print Thermal (Tersambung)';
        toast('Nota terkirim ke printer.', 'success');
    } catch (e) {
        if (e.name !== 'NotFoundError') {
            toast(e.message || 'Gagal mencetak via Bluetooth.', 'error');
        }
        document.getElementById('btPrintLabel').textContent = 'Print Thermal Bluetooth';
    } finally {
        btn.disabled = false;
        app.btPrinting = false;
    }
}

let searchTimer = null;
function searchDebounce(v) {
    app.filter.search = v;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(load, 300);
}

document.addEventListener('DOMContentLoaded', async () => {
    const [ghRes, jmRes] = await Promise.all([
        apiFetch('/api/greenhouse?semua=1'),
        apiFetch('/api/jenis-melon?semua=1'),
    ]);
    if (ghRes?.ok) {
        const d = await ghRes.json();
        app.greenhouses = d.data || [];
        const filterSel = document.getElementById('filterGh');
        const formSel   = document.getElementById('fGhId');
        app.greenhouses.forEach(gh => {
            filterSel.appendChild(new Option(gh.nama, gh.id));
            formSel.appendChild(new Option(gh.nama, gh.id));
        });
    }
    if (jmRes?.ok) { const d = await jmRes.json(); app.jenisList = d.data || []; }
    await load();
});
</script>
@endsection
