@extends('layouts.app')
@section('title', 'Greenhouse — Panen Melon')

@section('breadcrumb')
    <span>Greenhouse</span>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span style="color:var(--color-primary);">Panen Melon</span>
@endsection

@section('content')
<div>

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Panen Melon</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Catat hasil panen per greenhouse</p>
        </div>
        <button onclick="openCreate()" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Catat Panen
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <select id="filterGh" onchange="app.filter.greenhouse_id=this.value;load()" class="form-input w-auto min-w-[160px]">
            <option value="">Semua GH</option>
        </select>
        <select id="filterGrade" onchange="app.filter.grade=this.value;load()" class="form-input w-auto">
            <option value="">Semua Grade</option>
            <option value="A">Grade A</option>
            <option value="B">Grade B</option>
            <option value="C">Grade C</option>
            <option value="D">Grade D</option>
            <option value="E">Grade E</option>
        </select>
        <input type="date" id="filterDari" onchange="app.filter.dari=this.value;load()" class="form-input w-auto" placeholder="Dari"/>
        <input type="date" id="filterSampai" onchange="app.filter.sampai=this.value;load()" class="form-input w-auto" placeholder="Sampai"/>
        <button onclick="resetFilters()" class="btn btn-secondary text-xs">Reset</button>
    </div>

    {{-- Loading skeleton --}}
    <div id="skeleton" class="space-y-2">
        @for($i=0;$i<5;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div id="tableWrap" class="hidden table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Tanggal</th>
                    <th>Greenhouse</th>
                    <th class="hidden md:table-cell">Jenis Melon</th>
                    <th class="text-right">Berat (kg)</th>
                    <th class="text-center">Grade</th>
                    <th class="text-center hidden md:table-cell">Busuk</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbl"></tbody>
        </table>
        <div id="pageBar" class="hidden flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" id="pageInfo" style="color:var(--color-text-muted);"></p>
            <div class="flex gap-1">
                <button id="btnPrev" onclick="load(app.meta.current_page-1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button id="btnNext" onclick="load(app.meta.current_page+1)" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40" style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Form --}}
    <div id="formModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('formModal')"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--color-border);">
                <h3 id="formTitle" class="text-sm font-semibold" style="color:var(--color-text);">Catat Panen</h3>
                <button type="button" onclick="closeModal('formModal')" class="w-7 h-7 flex items-center justify-center rounded-lg" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)'" onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-5">
                <form id="panenForm" onsubmit="save(event)" class="space-y-4">
                    <div>
                        <label class="form-label">Greenhouse</label>
                        <select id="fGhId" required class="form-input">
                            <option value="">— Pilih GH —</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Jenis Melon</label>
                        <select id="fJenisId" required class="form-input">
                            <option value="">— Pilih Jenis —</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Berat (kg)</label>
                            <input type="number" id="fBerat" min="0.01" step="0.01" required class="form-input"/>
                        </div>
                        <div>
                            <label class="form-label">Grade</label>
                            <select id="fGrade" required class="form-input">
                                <option value="">— Grade —</option>
                                <option value="A">Grade A</option>
                                <option value="B">Grade B</option>
                                <option value="C">Grade C</option>
                                <option value="D">Grade D</option>
                                <option value="E">Grade E</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Tanggal Panen</label>
                        <input type="date" id="fTanggal" required class="form-input"/>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" id="fBusuk" class="w-4 h-4 rounded cursor-pointer" style="accent-color:#EF4444"/>
                        <label for="fBusuk" class="text-sm cursor-pointer" style="color:var(--color-text);">Buah busuk/afkir</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeModal('formModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                        <button type="submit" id="btnSave" class="btn btn-primary flex-1 justify-center">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div id="deleteModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="absolute inset-0" onclick="closeModal('deleteModal')"></div>
        <div class="relative w-full max-w-md rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
             style="background:var(--color-surface);border:1px solid var(--color-border);" onclick="event.stopPropagation()">
            <div class="px-5 py-5">
                <p class="text-sm font-medium mb-1 text-center" style="color:var(--color-text);">Hapus data panen ini?</p>
                <p class="text-xs mb-5 text-center" style="color:var(--color-text-muted);">Data akan diarsipkan.</p>
                <div class="flex gap-3">
                    <button onclick="closeModal('deleteModal')" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button id="btnDelete" onclick="doDelete()" class="btn btn-danger flex-1 justify-center">Hapus</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
const app = {
    items: [], loading: true, editId: null, deleteTarget: null, saving: false, deleting: false,
    greenhouses: [], jenisList: [],
    filter: { greenhouse_id:'', grade:'', dari:'', sampai:'' },
    meta: { current_page:1, last_page:1, from:1, to:1, total:0 }
};

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtKg(n) {
    return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits:2, maximumFractionDigits:2 });
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
function render() {
    const tbody = document.getElementById('tbl');
    if (!app.items.length) {
        tbody.innerHTML = `<tr><td colspan="8" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);"><p class="text-sm font-medium">Belum ada data panen</p></td></tr>`;
        document.getElementById('pageBar').classList.add('hidden');
        return;
    }
    tbody.innerHTML = app.items.map((p, idx) => `
        <tr class="tbl-row">
            <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);">${(app.meta.from || 1) + idx}</td>
            <td class="tbl-cell text-sm">${escHtml(formatDate(p.tanggal))}</td>
            <td class="tbl-cell text-sm font-medium">${escHtml(p.greenhouse?.nama || '—')}</td>
            <td class="tbl-cell text-sm hidden md:table-cell">${escHtml(p.jenis_melon?.nama || '—')}</td>
            <td class="tbl-cell text-right font-semibold" style="color:var(--color-primary);">${fmtKg(p.berat)}</td>
            <td class="tbl-cell text-center">
                <span class="badge ${p.grade === 'A' ? 'badge-success' : 'badge-neutral'}">${escHtml(p.grade_label || p.grade)}</span>
            </td>
            <td class="tbl-cell text-center hidden md:table-cell">
                ${p.is_busuk ? '<span class="badge badge-rose">Busuk</span>' : '<span class="text-xs" style="color:var(--color-text-muted);">—</span>'}
            </td>
            <td class="tbl-cell">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openEditById(${p.id})" title="Edit" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='var(--color-bg)';this.style.color='var(--color-primary)'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                    </button>
                    <button onclick="openDeleteById(${p.id})" title="Hapus" class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors" style="color:var(--color-text-muted);" onmouseover="this.style.background='#FEE2E2';this.style.color='#B91C1C'" onmouseout="this.style.background='';this.style.color='var(--color-text-muted)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    const bar = document.getElementById('pageBar');
    if (app.meta.last_page > 1) {
        bar.classList.remove('hidden');
        document.getElementById('pageInfo').textContent = `Menampilkan ${app.meta.from}–${app.meta.to} dari ${app.meta.total}`;
        document.getElementById('btnPrev').disabled = app.meta.current_page <= 1;
        document.getElementById('btnNext').disabled = app.meta.current_page >= app.meta.last_page;
    } else {
        bar.classList.add('hidden');
    }
}
async function load(page = 1) {
    setLoading(true);
    let url = `/api/panen-melon?page=${page}&per_page=20`;
    if (app.filter.greenhouse_id) url += `&greenhouse_id=${app.filter.greenhouse_id}`;
    if (app.filter.grade) url += `&grade=${app.filter.grade}`;
    if (app.filter.dari) url += `&dari=${app.filter.dari}`;
    if (app.filter.sampai) url += `&sampai=${app.filter.sampai}`;
    const res = await apiFetch(url);
    if (res?.ok) {
        const d = await res.json();
        app.items = d.data || [];
        app.meta = d.meta || { current_page:d.current_page, last_page:d.last_page, from:d.from, to:d.to, total:d.total };
    }
    setLoading(false);
    render();
}
function resetFilters() {
    app.filter = { greenhouse_id:'', grade:'', dari:'', sampai:'' };
    ['filterGh','filterGrade','filterDari','filterSampai'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    load();
}
function openCreate() {
    app.editId = null;
    document.getElementById('formTitle').textContent = 'Catat Panen';
    fillSelect('fGhId', app.greenhouses, 'id', 'nama', '');
    fillSelect('fJenisId', app.jenisList, 'id', 'nama', '');
    document.getElementById('fBerat').value = '';
    document.getElementById('fGrade').value = '';
    document.getElementById('fTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('fBusuk').checked = false;
    openModal('formModal');
}
function openEditById(id) {
    const p = app.items.find(x => x.id === id);
    if (!p) return;
    app.editId = id;
    document.getElementById('formTitle').textContent = 'Edit Panen';
    fillSelect('fGhId', app.greenhouses, 'id', 'nama', p.greenhouse_id);
    fillSelect('fJenisId', app.jenisList, 'id', 'nama', p.jenis_melon_id);
    document.getElementById('fBerat').value = p.berat;
    document.getElementById('fGrade').value = p.grade;
    document.getElementById('fTanggal').value = p.tanggal;
    document.getElementById('fBusuk').checked = !!p.is_busuk;
    openModal('formModal');
}
function openDeleteById(id) {
    app.deleteTarget = app.items.find(x => x.id === id);
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
        jenis_melon_id: document.getElementById('fJenisId').value,
        berat: document.getElementById('fBerat').value,
        grade: document.getElementById('fGrade').value,
        tanggal: document.getElementById('fTanggal').value,
        is_busuk: document.getElementById('fBusuk').checked,
    };
    const url = app.editId ? `/api/panen-melon/${app.editId}` : '/api/panen-melon';
    const res = await apiFetch(url, { method: app.editId ? 'PUT' : 'POST', body: JSON.stringify(form) });
    const data = await res.json();
    if (res.ok) {
        toast(app.editId ? 'Panen diperbarui.' : 'Panen dicatat.', 'success');
        closeModal('formModal');
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
    const res = await apiFetch(`/api/panen-melon/${app.deleteTarget.id}`, { method: 'DELETE' });
    if (res) {
        const data = await res.json();
        if (res.ok) {
            toast('Data panen dihapus.', 'success');
            closeModal('deleteModal');
            await load(app.meta.current_page);
        } else {
            toast(data.message || 'Gagal menghapus.', 'error');
        }
    }
    btn.disabled = false; btn.textContent = 'Hapus';
    app.deleting = false;
}
document.addEventListener('DOMContentLoaded', async () => {
    const [ghRes, jmRes] = await Promise.all([
        apiFetch('/api/greenhouse?semua=1'),
        apiFetch('/api/jenis-melon?semua=1'),
    ]);
    if (ghRes?.ok) { const d = await ghRes.json(); app.greenhouses = d.data || []; }
    if (jmRes?.ok) { const d = await jmRes.json(); app.jenisList = d.data || []; }
    fillSelect('filterGh', app.greenhouses, 'id', 'nama', '');
    await load();
});
</script>
@endsection
