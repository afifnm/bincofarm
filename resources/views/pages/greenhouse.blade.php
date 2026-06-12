@extends('layouts.app')
@section('title', 'Greenhouse — Master')

@section('breadcrumb')
    <span>Greenhouse</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Master Greenhouse</span>
@endsection

@section('content')
<div>

    {{-- Sub nav tabs --}}
    <div class="flex gap-1.5 mb-5">
        <a href="{{ route('greenhouse') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-primary);color:#fff;">
            Greenhouse
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('jenis-melon') }}"
           class="px-4 py-2 rounded-xl text-sm font-medium transition-colors"
           style="background:var(--color-surface);color:var(--color-text-muted);border:1px solid var(--color-border);"
           onmouseover="this.style.background='var(--color-bg)'"
           onmouseout="this.style.background='var(--color-surface)'">
            Jenis Melon
        </a>
        @endif
    </div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Master Greenhouse</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola data greenhouse dan penanggung jawab</p>
        </div>
        @if(auth()->user()->isAdmin())
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah GH
        </button>
        @endif
    </div>

    {{-- Search --}}
    <div class="mb-4 flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Cari greenhouse..." class="form-input pl-9"/>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div id="tableWrap" class="hidden table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Greenhouse</th>
                    <th class="hidden md:table-cell">Penanggung Jawab</th>
                    <th class="hidden lg:table-cell">Kas Terhubung</th>
                    <th class="text-center hidden md:table-cell">Pohon Hidup</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbl"></tbody>
        </table>
        <div id="pageBar" class="hidden flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" id="pageInfo" style="color:var(--color-text-muted);"></p>
            <div id="pageBtns" class="flex gap-1"></div>
        </div>
    </div>

    {{-- Modal Form GH (admin only) --}}
    @if(auth()->user()->isAdmin())
    <div id="formModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('formModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 id="formTitle" class="text-sm font-semibold" style="color:var(--color-text);">Tambah Greenhouse</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="ghForm" onsubmit="save(event)" class="space-y-4">
                    <div>
                        <label class="form-label">Nama Greenhouse</label>
                        <input type="text" id="fNama" required class="form-input" placeholder="Nama GH"/>
                    </div>
                    <div>
                        <label class="form-label">Lokasi</label>
                        <input type="text" id="fLokasi" class="form-input" placeholder="Lokasi (opsional)"/>
                    </div>
                    <div>
                        <label class="form-label">Penanggung Jawab</label>
                        <select id="fUserId" class="form-input">
                            <option value="">— Pilih User —</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Rekening Kas</label>
                        <select id="fKasId" required class="form-input">
                            <option value="">— Pilih Kas —</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="fIsActive" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer" checked/>
                        <label for="fIsActive" class="text-sm cursor-pointer" style="color:var(--color-text);">Greenhouse aktif</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn btn-primary flex-1 justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus (admin only) --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('deleteModal')"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 class="text-sm font-semibold" style="color:var(--color-text);">Hapus Greenhouse</h3>
                <button onclick="closeModal('deleteModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <div class="text-center py-2">
                    <div class="icon-wrap icon-wrap-rose mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </div>
                    <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Hapus <strong id="deleteTargetName"></strong>?</p>
                    <p class="text-xs mb-5" style="color:var(--color-text-muted);">Data greenhouse akan diarsipkan.</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="closeModal('deleteModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button id="btnDelete" onclick="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Populasi --}}
    <div id="popModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('popModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--color-text);">Update Populasi Pohon</h3>
                    <p class="text-xs" id="popTargetName" style="color:var(--color-text-muted);"></p>
                </div>
                <button onclick="closeModal('popModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="popForm" onsubmit="savePop(event)" class="space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-label">Total Pohon</label>
                            <input type="number" id="fTotalPohon" min="0" required class="form-input"/>
                        </div>
                        <div>
                            <label class="form-label">Pohon Hidup</label>
                            <input type="number" id="fPohonHidup" min="0" required class="form-input"/>
                        </div>
                        <div>
                            <label class="form-label">Pohon Mati</label>
                            <input type="number" id="fPohonMati" min="0" required class="form-input"/>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Catatan</label>
                        <input type="text" id="fPopCatatan" class="form-input" placeholder="Opsional"/>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeModal('popModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSavePop" class="btn btn-primary flex-1 justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Riwayat Pohon --}}
    <div id="historiModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('historiModal')"></div>
        <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col"
             style="background:var(--color-surface);border:1px solid var(--color-border);max-height:80vh;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b shrink-0" style="border-color:var(--color-border);">
                <div>
                    <h3 class="text-sm font-semibold" style="color:var(--color-text);">Riwayat Populasi Pohon</h3>
                    <p class="text-xs" id="historiTargetName" style="color:var(--color-text-muted);"></p>
                </div>
                <button onclick="closeModal('historiModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 p-5">
                <div id="historiLoading" class="hidden space-y-2">
                    <div class="skeleton rounded-xl h-10"></div>
                    <div class="skeleton rounded-xl h-10"></div>
                    <div class="skeleton rounded-xl h-10"></div>
                </div>
                <div id="historiEmpty" class="hidden text-center py-8" style="color:var(--color-text-muted);">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm">Belum ada riwayat perubahan</p>
                </div>
                <div id="historiContent" class="hidden">
                    <div class="table-wrap">
                        <table class="w-full text-xs">
                            <thead class="tbl-head">
                                <tr>
                                    <th>Waktu</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Hidup</th>
                                    <th class="text-right">Mati</th>
                                    <th class="hidden sm:table-cell">Catatan</th>
                                    <th class="hidden sm:table-cell">Oleh</th>
                                </tr>
                            </thead>
                            <tbody id="historiTbl"></tbody>
                        </table>
                    </div>
                    <div id="historiPageBar" class="hidden flex items-center justify-between mt-3">
                        <p class="text-xs" id="historiPageInfo" style="color:var(--color-text-muted);"></p>
                        <div class="flex gap-1">
                            <button id="historiPrev" onclick="loadHistori(app.historiMeta.current_page-1)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button id="historiNext" onclick="loadHistori(app.historiMeta.current_page+1)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const isAdmin = @json(auth()->user()->isAdmin());
const app = {
    items: [], loading: true, editId: null, deleteTarget: null, saving: false, deleting: false,
    search: '', users: [], kasList: [],
    popTarget: null,
    historiTarget: null, historiItems: [], historiLoading: false,
    historiMeta: { current_page:1, last_page:1, from:1, to:1, total:0 },
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDateTime(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
        + ' ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
}
function setLoading(v) {
    document.getElementById('skeleton').classList.toggle('hidden', !v);
    document.getElementById('tableWrap').classList.toggle('hidden', v);
}
function fillSelect(id, items, valueKey, labelKey, current) {
    const sel = document.getElementById(id);
    const blank = sel.options[0];
    sel.innerHTML = '';
    sel.appendChild(blank);
    items.forEach(it => {
        const o = new Option(it[labelKey], it[valueKey]);
        if (String(it[valueKey]) === String(current)) o.selected = true;
        sel.appendChild(o);
    });
}
function buildPageRange(cur, last) {
    if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);
    const pages = [];
    if (cur <= 4) { for (let i=1;i<=5;i++) pages.push(i); pages.push('...'); pages.push(last); }
    else if (cur >= last - 3) { pages.push(1); pages.push('...'); for (let i=last-4;i<=last;i++) pages.push(i); }
    else { pages.push(1); pages.push('...'); pages.push(cur-1); pages.push(cur); pages.push(cur+1); pages.push('...'); pages.push(last); }
    return pages;
}

function render() {
    const tbody = document.getElementById('tbl');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);"><svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg><p class="text-sm font-medium">Belum ada greenhouse</p></td></tr>`;
        document.getElementById('pageBar').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.items.map((gh, idx) => {
        const adminBtns = isAdmin ? `
            <button onclick="openEditById(${gh.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
            </button>
            <button onclick="openDeleteById(${gh.id})" title="Hapus" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </button>` : '';
        return `
            <tr class="tbl-row">
                <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from || 1) + idx}</td>
                <td class="tbl-cell">
                    <p class="font-semibold text-sm" style="color:var(--color-text);">${escHtml(gh.nama)}</p>
                    <p class="text-xs" style="color:var(--color-text-muted);">${escHtml(gh.lokasi || '—')}</p>
                </td>
                <td class="tbl-cell hidden md:table-cell text-sm">${escHtml(gh.user?.name || '—')}</td>
                <td class="tbl-cell hidden lg:table-cell text-sm">${escHtml(gh.kas?.nama || '—')}</td>
                <td class="tbl-cell text-center hidden md:table-cell">
                    <span class="text-sm font-medium" style="color:var(--color-primary);">${gh.populasi ? escHtml(gh.populasi.pohon_hidup) : '—'}</span>
                </td>
                <td class="tbl-cell text-center">
                    <span class="badge ${gh.is_active ? 'badge-success' : 'badge-neutral'}">${gh.is_active ? 'Aktif' : 'Nonaktif'}</span>
                </td>
                <td class="tbl-cell">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="openPopulasiById(${gh.id})" title="Update Populasi" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='#059669'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        </button>
                        <button onclick="openHistoriById(${gh.id})" title="Riwayat Pohon" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </button>
                        ${adminBtns}
                    </div>
                </td>
            </tr>`;
    }).join('');

    const bar = document.getElementById('pageBar');
    if (app.meta.last_page > 1) {
        bar.classList.remove('hidden');
        document.getElementById('pageInfo').textContent = `Menampilkan ${app.meta.from}–${app.meta.to} dari ${app.meta.total} data`;
        const pages = buildPageRange(app.meta.current_page, app.meta.last_page);
        document.getElementById('pageBtns').innerHTML =
            `<button onclick="goPage(${app.meta.current_page-1})" ${app.meta.current_page<=1?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>` +
            pages.map(p => p === '...'
                ? `<button disabled class="w-8 h-8 flex items-center justify-center rounded-lg text-xs" style="border:1px solid var(--color-border);">...</button>`
                : `<button onclick="goPage(${p})" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs" style="${p===app.meta.current_page?'background:var(--color-primary);color:#fff;':'border:1px solid var(--color-border);'}">${p}</button>`
            ).join('') +
            `<button onclick="goPage(${app.meta.current_page+1})" ${app.meta.current_page>=app.meta.last_page?'disabled':''} class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>`;
    } else {
        bar.classList.add('hidden');
    }
}

async function load(page = 1) {
    setLoading(true);
    let url = `/api/greenhouse?page=${page}&per_page=15`;
    if (app.search) url += `&search=${encodeURIComponent(app.search)}`;
    const res = await apiFetch(url);
    if (res?.ok) {
        const d = await res.json();
        app.items = d.data || [];
        app.meta = d.meta || { current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
    }
    setLoading(false);
    render();
}

function goPage(p) { if (p !== '...' && p >= 1 && p <= app.meta.last_page) load(p); }

function openCreate() {
    app.editId = null;
    document.getElementById('formTitle').textContent = 'Tambah Greenhouse';
    document.getElementById('fNama').value = '';
    document.getElementById('fLokasi').value = '';
    fillSelect('fUserId', app.users, 'id', 'name', '');
    fillSelect('fKasId', app.kasList, 'id', 'nama', '');
    document.getElementById('fIsActive').checked = true;
    openModal('formModal');
}
function openEditById(id) {
    const gh = app.items.find(x => x.id === id);
    if (!gh) return;
    app.editId = id;
    document.getElementById('formTitle').textContent = 'Edit Greenhouse';
    document.getElementById('fNama').value = gh.nama;
    document.getElementById('fLokasi').value = gh.lokasi || '';
    fillSelect('fUserId', app.users, 'id', 'name', gh.user_id || '');
    fillSelect('fKasId', app.kasList, 'id', 'nama', gh.kas_id);
    document.getElementById('fIsActive').checked = !!gh.is_active;
    openModal('formModal');
}
function openDeleteById(id) {
    app.deleteTarget = app.items.find(x => x.id === id);
    document.getElementById('deleteTargetName').textContent = app.deleteTarget?.nama || '';
    openModal('deleteModal');
}
function openPopulasiById(id) {
    const gh = app.items.find(x => x.id === id);
    if (!gh) return;
    app.popTarget = gh;
    document.getElementById('popTargetName').textContent = 'GH: ' + (gh.nama || '');
    document.getElementById('fTotalPohon').value = gh.populasi?.total_pohon ?? 0;
    document.getElementById('fPohonHidup').value = gh.populasi?.pohon_hidup ?? 0;
    document.getElementById('fPohonMati').value  = gh.populasi?.pohon_mati  ?? 0;
    document.getElementById('fPopCatatan').value = '';
    openModal('popModal');
}
function openHistoriById(id) {
    const gh = app.items.find(x => x.id === id);
    if (!gh) return;
    app.historiTarget = gh;
    app.historiItems = [];
    document.getElementById('historiTargetName').textContent = gh.nama || '';
    openModal('historiModal');
    loadHistori(1);
}

async function save(e) {
    e.preventDefault();
    if (app.saving) return;
    app.saving = true;
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const userId = document.getElementById('fUserId').value;
    const payload = {
        nama: document.getElementById('fNama').value,
        lokasi: document.getElementById('fLokasi').value,
        user_id: userId || null,
        kas_id: document.getElementById('fKasId').value,
        is_active: document.getElementById('fIsActive').checked,
    };
    const url = app.editId ? `/api/greenhouse/${app.editId}` : '/api/greenhouse';
    const res = await apiFetch(url, { method: app.editId ? 'PUT' : 'POST', body: JSON.stringify(payload) });
    const data = await res.json();
    if (res.ok) {
        toast(app.editId ? 'Greenhouse diperbarui.' : 'Greenhouse ditambah.', 'success');
        closeModal('formModal');
        await load(app.meta.current_page);
    } else {
        toast(data.errors ? Object.values(data.errors).flat().join(' ') : data.message, 'error');
    }
    btn.disabled = false; btn.textContent = 'Simpan';
    app.saving = false;
}

async function savePop(e) {
    e.preventDefault();
    if (app.saving) return;
    app.saving = true;
    const btn = document.getElementById('btnSavePop');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const payload = {
        total_pohon: document.getElementById('fTotalPohon').value,
        pohon_hidup: document.getElementById('fPohonHidup').value,
        pohon_mati:  document.getElementById('fPohonMati').value,
        catatan:     document.getElementById('fPopCatatan').value,
    };
    const res = await apiFetch(`/api/greenhouse/${app.popTarget.id}/populasi`, { method: 'PUT', body: JSON.stringify(payload) });
    const data = await res.json();
    if (res.ok) {
        toast('Populasi diperbarui.', 'success');
        closeModal('popModal');
        await load(app.meta.current_page);
    } else {
        toast(data.errors ? Object.values(data.errors).flat().join(' ') : data.message, 'error');
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
        const res = await apiFetch(`/api/greenhouse/${app.deleteTarget.id}`, { method: 'DELETE' });
        if (!res) return;
        const data = await res.json();
        if (res.ok) {
            toast('Greenhouse dihapus.', 'success');
            closeModal('deleteModal');
            await load(app.meta.current_page);
        } else {
            toast(data.message || 'Gagal menghapus.', 'error');
        }
    } catch (err) {
        toast('Terjadi kesalahan.', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Hapus';
        app.deleting = false;
    }
}

function renderHistori() {
    document.getElementById('historiLoading').classList.toggle('hidden', !app.historiLoading);
    document.getElementById('historiEmpty').classList.add('hidden');
    document.getElementById('historiContent').classList.add('hidden');
    if (app.historiLoading) return;
    if (!app.historiItems.length) {
        document.getElementById('historiEmpty').classList.remove('hidden');
        return;
    }
    document.getElementById('historiContent').classList.remove('hidden');
    document.getElementById('historiTbl').innerHTML = app.historiItems.map(h => `
        <tr class="tbl-row">
            <td class="tbl-cell text-xs" style="color:var(--color-text-muted);">${formatDateTime(h.created_at)}</td>
            <td class="tbl-cell text-right">
                ${h.total_pohon_lama !== null ? `<span class="text-xs" style="color:var(--color-text-muted);">${escHtml(h.total_pohon_lama)}</span><span class="text-xs mx-1" style="color:var(--color-text-muted);">→</span>` : ''}
                <span class="font-semibold" style="color:var(--color-text);">${escHtml(h.total_pohon_baru)}</span>
            </td>
            <td class="tbl-cell text-right">
                ${h.pohon_hidup_lama !== null ? `<span class="text-xs" style="color:var(--color-text-muted);">${escHtml(h.pohon_hidup_lama)}</span><span class="text-xs mx-1" style="color:var(--color-text-muted);">→</span>` : ''}
                <span class="font-semibold" style="color:#059669;">${escHtml(h.pohon_hidup_baru)}</span>
            </td>
            <td class="tbl-cell text-right">
                ${h.pohon_mati_lama !== null ? `<span class="text-xs" style="color:var(--color-text-muted);">${escHtml(h.pohon_mati_lama)}</span><span class="text-xs mx-1" style="color:var(--color-text-muted);">→</span>` : ''}
                <span class="font-semibold" style="color:#DC2626;">${escHtml(h.pohon_mati_baru)}</span>
            </td>
            <td class="tbl-cell hidden sm:table-cell" style="color:var(--color-text-muted);">${escHtml(h.catatan || '—')}</td>
            <td class="tbl-cell hidden sm:table-cell" style="color:var(--color-text-muted);">${escHtml(h.user?.name || '—')}</td>
        </tr>
    `).join('');
    const bar = document.getElementById('historiPageBar');
    if (app.historiMeta.last_page > 1) {
        bar.classList.remove('hidden');
        document.getElementById('historiPageInfo').textContent = `${app.historiMeta.from}–${app.historiMeta.to} dari ${app.historiMeta.total}`;
        document.getElementById('historiPrev').disabled = app.historiMeta.current_page <= 1;
        document.getElementById('historiNext').disabled = app.historiMeta.current_page >= app.historiMeta.last_page;
    } else {
        bar.classList.add('hidden');
    }
}

async function loadHistori(page = 1) {
    app.historiLoading = true;
    renderHistori();
    const res = await apiFetch(`/api/greenhouse/${app.historiTarget.id}/populasi/histori?page=${page}&per_page=15`);
    if (res?.ok) {
        const d = await res.json();
        app.historiItems = d.data || [];
        app.historiMeta = d.meta || { current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
    }
    app.historiLoading = false;
    renderHistori();
}

let searchTimer = null;
document.addEventListener('DOMContentLoaded', async () => {
    if (isAdmin) {
        const [usersRes, kasRes] = await Promise.all([
            apiFetch('/api/users'),
            apiFetch('/api/kas?semua=0&per_page=100'),
        ]);
        if (usersRes?.ok) { const d = await usersRes.json(); app.users = d.data || []; }
        if (kasRes?.ok)   { const d = await kasRes.json();   app.kasList = d.data || []; }
    }
    document.getElementById('searchInput').addEventListener('input', function() {
        app.search = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(load, 300);
    });
    await load();
});
</script>
@endsection
