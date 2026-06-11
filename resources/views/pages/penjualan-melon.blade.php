@extends('layouts.app')
@section('title', 'Greenhouse — Penjualan Melon')

@push('styles')
<style>
    /* Nota struk: hanya area nota yang tercetak */
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
<div x-data="penjualanMelonApp()" x-init="init()">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs mb-4" style="color:var(--color-text-muted);">
        <span>Greenhouse</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-primary);">Penjualan Melon</span>
    </nav>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Penjualan Melon</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Satu nota bisa berisi beberapa jenis melon. Kas masuk tercatat otomatis</p>
        </div>
        <button @click="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Penjualan
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="mb-4 flex flex-wrap items-center gap-2 md:gap-3">
        <div class="relative flex-1 min-w-[150px] md:flex-none md:w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" x-model="filter.search" @input.debounce.300ms="load()" placeholder="Cari pembeli / no nota..."
                   class="form-input pl-9 w-full"/>
        </div>
        <select x-model="filter.greenhouse_id" @change="load()" class="form-input w-auto min-w-[130px]">
            <option value="">Semua GH</option>
            <template x-for="gh in greenhouses" :key="gh.id">
                <option :value="gh.id" x-text="gh.nama"></option>
            </template>
        </select>
        <input type="date" x-model="filter.dari" @change="load()" class="form-input w-auto" placeholder="Dari"/>
        <input type="date" x-model="filter.sampai" @change="load()" class="form-input w-auto" placeholder="Sampai"/>
        <button @click="filter={search:'',greenhouse_id:'',dari:'',sampai:''};load()" class="btn btn-secondary text-xs">Reset</button>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- ══════ Desktop table (md+) ══════ --}}
    <div x-show="!loading" x-cloak class="table-wrap hidden md:block">
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
            <tbody>
                <template x-for="pj in items" :key="pj.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-xs font-mono font-semibold" style="color:var(--color-primary);" x-text="pj.no_nota || '—'"></td>
                        <td class="tbl-cell text-sm" x-text="formatDate(pj.tanggal)"></td>
                        <td class="tbl-cell text-sm" x-text="pj.greenhouse?.nama || '—'"></td>
                        <td class="tbl-cell text-sm font-medium" x-text="pj.nama_pembeli"></td>
                        <td class="tbl-cell text-xs hidden lg:table-cell" style="color:var(--color-text-muted);"
                            x-text="(pj.items || []).map(i => `${i.jenis_melon?.nama || '?'} ${fmtKg(i.jumlah_kg)}kg`).join(', ')"></td>
                        <td class="tbl-cell text-right hidden lg:table-cell" x-text="fmtKg(pj.total_kg)"></td>
                        <td class="tbl-cell text-right font-bold" style="color:var(--color-primary);" x-text="'Rp ' + fmtRp(pj.total)"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="openNota(pj)" title="Cetak Nota"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='#059669'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                                </button>
                                <button @click="openEdit(pj)" title="Edit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                                        style="color:var(--color-text-muted);"
                                        onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'"
                                        onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                </button>
                                <button @click="openDelete(pj)" title="Hapus"
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
                        <p class="text-sm font-medium">Belum ada data penjualan</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ══════ Mobile card list (< md) ══════ --}}
    <div x-show="!loading" x-cloak class="md:hidden space-y-3">
        <template x-for="pj in items" :key="pj.id">
            <div class="rounded-2xl p-4" style="background:var(--color-surface);border:1px solid var(--color-border);">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <p class="text-xs font-mono font-semibold" style="color:var(--color-primary);" x-text="pj.no_nota || '—'"></p>
                        <p class="text-sm font-semibold mt-0.5 truncate" style="color:var(--color-text);" x-text="pj.nama_pembeli"></p>
                        <p class="text-xs" style="color:var(--color-text-muted);">
                            <span x-text="formatDate(pj.tanggal)"></span> · <span x-text="pj.greenhouse?.nama || '—'"></span>
                        </p>
                    </div>
                    <p class="text-base font-bold shrink-0" style="color:var(--color-primary);" x-text="'Rp ' + fmtRp(pj.total)"></p>
                </div>
                <div class="rounded-xl px-3 py-2 mb-3 space-y-1" style="background:var(--color-bg);">
                    <template x-for="it in (pj.items || [])" :key="it.id">
                        <div class="flex justify-between text-xs">
                            <span style="color:var(--color-text);" x-text="`${it.jenis_melon?.nama || '?'} — ${fmtKg(it.jumlah_kg)} kg × Rp ${fmtRp(it.harga_per_kg)}`"></span>
                            <span class="font-medium shrink-0 pl-2" style="color:var(--color-text);" x-text="'Rp ' + fmtRp(it.subtotal)"></span>
                        </div>
                    </template>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="openNota(pj)" class="btn btn-secondary justify-center text-xs py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                        Nota
                    </button>
                    <button @click="openEdit(pj)" class="btn btn-secondary justify-center text-xs py-2.5">Edit</button>
                    <button @click="openDelete(pj)" class="btn btn-secondary justify-center text-xs py-2.5" style="color:#B91C1C;">Hapus</button>
                </div>
            </div>
        </template>
        <div x-show="items.length===0" class="rounded-2xl py-12 text-center" style="background:var(--color-surface);border:1px solid var(--color-border);color:var(--color-text-muted);">
            <p class="text-sm font-medium">Belum ada data penjualan</p>
        </div>
    </div>

    {{-- Pagination (shared) --}}
    <div x-show="!loading && meta.last_page > 1" x-cloak class="flex items-center justify-between mt-4 px-1">
        <p class="text-xs" style="color:var(--color-text-muted);">
            <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span>
        </p>
        <div class="flex gap-1">
            <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page <= 1" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);background:var(--color-surface);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page" class="w-9 h-9 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);background:var(--color-surface);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- ══════ Modal Form (multi-item) ══════ --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal maxWidth="max-w-2xl">
            <template x-if="open">
                <div>
                    <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);" x-text="editId ? 'Edit Penjualan' : 'Tambah Penjualan'"></h3>
                    <form @submit.prevent="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Greenhouse</label>
                                <select x-model="form.greenhouse_id" required class="form-input" :disabled="!!editId">
                                    <option value="">— Pilih GH —</option>
                                    <template x-for="gh in greenhouses" :key="gh.id">
                                        <option :value="gh.id" x-text="gh.nama"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Tanggal</label>
                                <input type="date" x-model="form.tanggal" required class="form-input"/>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Nama Pembeli</label>
                            <input type="text" x-model="form.nama_pembeli" required class="form-input" placeholder="Nama pembeli"/>
                        </div>

                        {{-- Item rows --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="form-label mb-0">Item Melon</label>
                                <button type="button" @click="addItem()" class="btn btn-secondary text-xs py-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Tambah Item
                                </button>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(item, idx) in form.items" :key="idx">
                                    <div class="rounded-xl p-3" style="background:var(--color-bg);border:1px solid var(--color-border);">
                                        <div class="flex items-center gap-2 mb-2">
                                            <select x-model="item.jenis_melon_id" required class="form-input flex-1">
                                                <option value="">— Jenis Melon —</option>
                                                <template x-for="jm in jenisList" :key="jm.id">
                                                    <option :value="jm.id" x-text="jm.nama"></option>
                                                </template>
                                            </select>
                                            <button type="button" @click="removeItem(idx)" :disabled="form.items.length === 1"
                                                    class="w-9 h-9 flex items-center justify-center rounded-lg shrink-0 disabled:opacity-30"
                                                    style="color:#B91C1C;border:1px solid var(--color-border);" title="Hapus item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="text-xs" style="color:var(--color-text-muted);">Jumlah (kg)</label>
                                                <input type="number" x-model="item.jumlah_kg" min="0.01" step="0.01" required class="form-input" placeholder="0.00"/>
                                            </div>
                                            <div>
                                                <label class="text-xs" style="color:var(--color-text-muted);">Harga/kg (Rp)</label>
                                                <input type="number" x-model="item.harga_per_kg" min="0" step="1" required class="form-input" placeholder="0"/>
                                            </div>
                                        </div>
                                        <p class="text-right text-xs mt-2 font-medium" style="color:var(--color-primary);"
                                           x-text="'Subtotal: Rp ' + fmtRp(subtotalItem(item))"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl px-4 py-3 flex items-center justify-between" style="background:var(--color-primary-soft);">
                            <div>
                                <p class="text-xs" style="color:var(--color-text-muted);">Total (<span x-text="fmtKg(totalKgForm)"></span> kg)</p>
                                <p class="text-lg font-bold" style="color:var(--color-primary);" x-text="'Rp ' + fmtRp(totalForm)"></p>
                            </div>
                            <p class="text-xs" style="color:var(--color-text-muted);" x-text="form.items.length + ' item'"></p>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                            <button type="submit" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                                <span x-show="!saving">Simpan</span>
                                <span x-show="saving" x-cloak>Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </x-modal>
    </div>

    {{-- ══════ Modal Nota ══════ --}}
    <div x-show="notaOpen" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal maxWidth="max-w-sm" closeExpr="notaOpen = false">
            <template x-if="notaOpen">
                <div>
                    <h3 class="text-base font-semibold mb-4 text-center" style="color:var(--color-text);">Nota Penjualan</h3>

                    {{-- Preview struk --}}
                    <div class="rounded-xl p-3 mb-4 overflow-x-auto" style="background:#fff;border:1px solid var(--color-border);">
                        <pre class="mx-auto" style="font-family:'Courier New',monospace;font-size:11px;line-height:1.3;color:#000;width:max-content;" x-text="notaText"></pre>
                    </div>

                    <div class="space-y-2">
                        <button @click="printBluetooth()" :disabled="btPrinting" class="btn btn-primary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.25 6.087l5.25 4.5-7.5 6.426V3.087l7.5 6.426-5.25 4.5M4.5 8.25l4.875 4.125L4.5 16.5"/></svg>
                            <span x-show="!btPrinting" x-text="btReady ? 'Print Thermal (Tersambung)' : 'Print Thermal Bluetooth'"></span>
                            <span x-show="btPrinting" x-cloak>Mencetak...</span>
                        </button>
                        <button @click="printBrowser()" class="btn btn-secondary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                            Print via Browser
                        </button>
                        <button @click="notaOpen=false" class="btn btn-secondary w-full justify-center" style="border:none;">Tutup</button>
                    </div>
                </div>
            </template>
        </x-modal>
    </div>

    {{-- Modal Hapus --}}
    <div x-show="confirmDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         @keydown.escape.window="confirmDelete=false">
        <div class="absolute inset-0" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);" @click="confirmDelete=false"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl" style="background:var(--color-surface);border:1px solid var(--color-border);" @click.stop>
            <div class="px-5 py-5">
                <p class="text-sm font-medium mb-1 text-center" style="color:var(--color-text);">
                    Hapus nota <span class="font-mono" x-text="deleteTarget?.no_nota"></span>?
                </p>
                <p class="text-xs mb-5 text-center" style="color:var(--color-text-muted);">Transaksi kas terkait akan di-void secara otomatis.</p>
                <div class="flex gap-3">
                    <button @click="confirmDelete=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button @click="doDelete()" :disabled="deleting" class="btn btn-danger flex-1 justify-center">
                        <template x-if="!deleting"><span>Hapus</span></template>
                        <template x-if="deleting"><span>Menghapus...</span></template>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Area cetak nota (hanya tampil saat print) --}}
<div id="nota-print"><pre id="nota-print-text"></pre></div>

<script>
/* ═══════════ ESC/POS + Web Bluetooth thermal printer ═══════════ */
const ThermalPrinter = {
    COLS: 32,
    device: null,
    characteristic: null,

    SERVICES: [
        '000018f0-0000-1000-8000-00805f9b34fb', // umum: printer thermal BLE generik
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Goojprt / Xprinter / PT-210
        '49535343-fe7d-4ae5-8fa9-9fafd205e455', // ISSC transparent UART (MTP-II dll.)
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
            } catch (e) { /* service tidak ada di printer ini, coba berikutnya */ }
        }
        device.gatt.disconnect();
        throw new Error('Printer tersambung tapi karakteristik tulis tidak ditemukan.');
    },

    async write(bytes) {
        await this.connect();
        const CHUNK = 100; // batas aman MTU BLE
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

    // lines: [{text, align: 'left'|'center', bold, big}]
    buildEscPos(lines) {
        const bytes = [0x1B, 0x40]; // init
        const push  = (...b) => bytes.push(...b);
        const pushText = (t) => {
            for (const c of t) {
                const code = c.charCodeAt(0);
                push(code >= 32 && code <= 126 ? code : 0x20);
            }
        };
        for (const line of lines) {
            push(0x1B, 0x61, line.align === 'center' ? 1 : 0);   // ESC a
            push(0x1B, 0x45, line.bold ? 1 : 0);                 // ESC E
            push(0x1D, 0x21, line.big ? 0x11 : 0x00);            // GS ! (2x w+h)
            pushText(line.text);
            push(0x0A);
        }
        push(0x1D, 0x21, 0x00, 0x1B, 0x45, 0x00, 0x1B, 0x61, 0x00);
        push(0x0A, 0x0A, 0x0A, 0x0A); // feed agar mudah disobek
        return new Uint8Array(bytes);
    },
};
</script>

<script>
function penjualanMelonApp() {
    return {
        items: [], loading: true, open: false, saving: false,
        confirmDelete: false, deleteTarget: null, editId: null, deleting: false,
        greenhouses: [], jenisList: [],
        notaOpen: false, notaTarget: null, notaText: '', btPrinting: false, btReady: false,
        filter: { search:'', greenhouse_id:'', dari:'', sampai:'' },
        meta: { current_page:1, last_page:1, from:1, to:1, total:0 },
        form: { greenhouse_id:'', nama_pembeli:'', tanggal:'', items: [] },

        fmtRp(n) { return Number(n || 0).toLocaleString('id-ID'); },
        fmtKg(n) { return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },

        async init() {
            const [ghRes, jmRes] = await Promise.all([
                apiFetch('/api/greenhouse?semua=1'),
                apiFetch('/api/jenis-melon?semua=1'),
            ]);
            if (ghRes?.ok) { const d = await ghRes.json(); this.greenhouses = d.data || []; }
            if (jmRes?.ok) { const d = await jmRes.json(); this.jenisList = d.data || []; }
            await this.load();
        },

        async load(page = 1) {
            this.loading = true;
            let url = `/api/penjualan-melon?page=${page}&per_page=20`;
            if (this.filter.search) url += `&search=${encodeURIComponent(this.filter.search)}`;
            if (this.filter.greenhouse_id) url += `&greenhouse_id=${this.filter.greenhouse_id}`;
            if (this.filter.dari) url += `&dari=${this.filter.dari}`;
            if (this.filter.sampai) url += `&sampai=${this.filter.sampai}`;
            const res = await apiFetch(url);
            if (res?.ok) {
                const d = await res.json();
                this.items = d.data || [];
                if (d.meta) this.meta = d.meta;
                else this.meta = { current_page: d.current_page, last_page: d.last_page, from: d.from, to: d.to, total: d.total };
            }
            this.loading = false;
        },

        goPage(p) { if (p >= 1 && p <= this.meta.last_page) this.load(p); },

        /* ── Form multi-item ── */
        emptyItem() { return { jenis_melon_id:'', jumlah_kg:'', harga_per_kg:'' }; },
        addItem() { this.form.items.push(this.emptyItem()); },
        removeItem(idx) { if (this.form.items.length > 1) this.form.items.splice(idx, 1); },
        subtotalItem(item) { return (parseFloat(item.jumlah_kg) || 0) * (parseFloat(item.harga_per_kg) || 0); },
        get totalForm() { return this.form.items.reduce((s, i) => s + this.subtotalItem(i), 0); },
        get totalKgForm() { return this.form.items.reduce((s, i) => s + (parseFloat(i.jumlah_kg) || 0), 0); },

        openCreate() {
            this.editId = null;
            this.form = {
                greenhouse_id: this.greenhouses.length === 1 ? this.greenhouses[0].id : '',
                nama_pembeli: '', tanggal: new Date().toISOString().split('T')[0],
                items: [this.emptyItem()],
            };
            this.open = true;
        },

        openEdit(pj) {
            this.editId = pj.id;
            this.form = {
                greenhouse_id: pj.greenhouse_id,
                nama_pembeli:  pj.nama_pembeli,
                tanggal:       pj.tanggal,
                items: (pj.items || []).map(i => ({
                    jenis_melon_id: i.jenis_melon_id,
                    jumlah_kg:      i.jumlah_kg,
                    harga_per_kg:   i.harga_per_kg,
                })),
            };
            if (this.form.items.length === 0) this.form.items = [this.emptyItem()];
            this.open = true;
        },

        openDelete(pj) { this.deleteTarget = pj; this.deleting = false; this.confirmDelete = true; },

        async save() {
            this.saving = true;
            const url    = this.editId ? `/api/penjualan-melon/${this.editId}` : '/api/penjualan-melon';
            const method = this.editId ? 'PUT' : 'POST';
            const res    = await apiFetch(url, { method, body: JSON.stringify(this.form) });
            const data   = await res.json();
            if (res.ok) {
                toast(this.editId ? 'Penjualan diperbarui.' : 'Penjualan disimpan & kas diperbarui.', 'success');
                this.open = false;
                await this.load(this.meta.current_page);
                // Tawarkan cetak nota setelah simpan baru
                if (!this.editId && data?.id) {
                    const saved = this.items.find(i => i.id === data.id) || data;
                    this.openNota(saved);
                }
            } else {
                const errs = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                toast(errs, 'error');
            }
            this.saving = false;
        },

        async doDelete() {
            this.deleting = true;
            try {
                const res = await apiFetch(`/api/penjualan-melon/${this.deleteTarget.id}`, { method: 'DELETE' });
                if (!res) return;
                const data = await res.json();
                if (res.ok) {
                    toast('Penjualan dihapus & transaksi kas di-void.', 'success');
                    this.confirmDelete = false;
                    await this.load(this.meta.current_page);
                } else {
                    toast(data.message || 'Gagal menghapus.', 'error');
                }
            } finally {
                this.deleting = false;
            }
        },

        /* ── Nota / struk ── */
        notaLines(pj) {
            const C = ThermalPrinter.COLS;
            const div = '-'.repeat(C);
            const kv  = (k, v) => (k + ': ').padEnd(9) + String(v ?? '—');
            const lr  = (l, r) => {
                l = String(l); r = String(r);
                const pad = C - l.length - r.length;
                return pad > 0 ? l + ' '.repeat(pad) + r : l + ' ' + r;
            };
            const tglParts = (pj.tanggal || '').split('-');
            const tgl = tglParts.length === 3 ? `${tglParts[2]}/${tglParts[1]}/${tglParts[0]}` : pj.tanggal;

            const lines = [
                { text: 'BINCOFARM', align: 'center', bold: true, big: true },
                { text: 'GH ' + (pj.greenhouse?.nama || '-'), align: 'center' },
            ];
            if (pj.greenhouse?.lokasi) lines.push({ text: pj.greenhouse.lokasi, align: 'center' });
            lines.push({ text: div });
            lines.push({ text: kv('No', pj.no_nota) });
            lines.push({ text: kv('Tgl', tgl) });
            lines.push({ text: kv('Pembeli', pj.nama_pembeli) });
            if (pj.user?.name) lines.push({ text: kv('Petugas', pj.user.name) });
            lines.push({ text: div });
            for (const it of (pj.items || [])) {
                lines.push({ text: it.jenis_melon?.nama || 'Melon' });
                lines.push({ text: lr(`  ${this.fmtKg(it.jumlah_kg)}kg x ${this.fmtRp(it.harga_per_kg)}`, this.fmtRp(it.subtotal)) });
            }
            lines.push({ text: div });
            lines.push({ text: lr('TOTAL KG', this.fmtKg(pj.total_kg) + ' kg') });
            lines.push({ text: lr('TOTAL', 'Rp ' + this.fmtRp(pj.total)), bold: true });
            lines.push({ text: div });
            lines.push({ text: 'Terima kasih atas', align: 'center' });
            lines.push({ text: 'pembelian Anda!', align: 'center' });
            return lines;
        },

        notaToText(lines) {
            const C = ThermalPrinter.COLS;
            return lines.map(l => {
                if (l.align === 'center') {
                    const w = l.big ? Math.floor(C / 2) : C;
                    const pad = Math.max(0, Math.floor((w - l.text.length) / 2));
                    return ' '.repeat(pad) + l.text;
                }
                return l.text;
            }).join('\n');
        },

        openNota(pj) {
            this.notaTarget = pj;
            this.notaText   = this.notaToText(this.notaLines(pj));
            this.btReady    = ThermalPrinter.connected;
            this.notaOpen   = true;
        },

        printBrowser() {
            document.getElementById('nota-print-text').textContent = this.notaText;
            window.print();
        },

        async printBluetooth() {
            this.btPrinting = true;
            try {
                const bytes = ThermalPrinter.buildEscPos(this.notaLines(this.notaTarget));
                await ThermalPrinter.write(bytes);
                this.btReady = true;
                toast('Nota terkirim ke printer.', 'success');
            } catch (e) {
                if (e.name !== 'NotFoundError') { // user batal pilih device
                    toast(e.message || 'Gagal mencetak via Bluetooth.', 'error');
                }
            } finally {
                this.btPrinting = false;
            }
        },
    }
}
</script>
@endsection
