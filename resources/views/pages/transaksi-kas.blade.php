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
            <button @click="openCreate('masuk')" class="btn btn-sm btn-success">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                Masuk
            </button>
            <button @click="openCreate('keluar')" class="btn btn-sm btn-danger">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                Keluar
            </button>
            <button @click="openTransfer()" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Transfer
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2 mb-5 p-3 rounded-xl border" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex-1 min-w-32">
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

    {{-- Loading --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<5;$i++)
        <div class="skeleton rounded-2xl h-16"></div>
        @endfor
    </div>

    {{-- Transaction list --}}
    <div x-show="!loading" x-cloak class="space-y-2">
        <template x-for="trx in items" :key="trx.id">
            <div class="card card-p flex items-center justify-between gap-3"
                 :class="trx.is_void ? 'void-row' : ''">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="['masuk','transfer_masuk'].includes(trx.tipe) ? 'icon-wrap-emerald' : 'icon-wrap-rose'">
                        <svg x-show="['masuk','transfer_masuk'].includes(trx.tipe)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                        <svg x-show="!['masuk','transfer_masuk'].includes(trx.tipe)" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:var(--color-text);" x-text="trx.nomor"></p>
                        <p class="text-xs truncate mt-0.5" style="color:var(--color-text-muted);"
                           x-text="(trx.kas?.nama||'-') + (trx.kategori?' · '+trx.kategori.nama:'') + (trx.keterangan?' — '+trx.keterangan:'')"></p>
                        <p class="text-xs mt-0.5" style="color:var(--color-text-muted);" x-text="trx.tanggal"></p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold"
                       :style="['masuk','transfer_masuk'].includes(trx.tipe)?'color:var(--color-success);':'color:var(--color-danger);'"
                       x-text="((['masuk','transfer_masuk'].includes(trx.tipe))?'+':'-') + formatRp(trx.jumlah)">
                    </p>
                    <span x-show="trx.is_void" class="badge badge-danger mt-1">VOID</span>
                    <button x-show="!trx.is_void" @click="openVoid(trx)"
                            class="btn btn-sm mt-1" style="background:#FEE2E2;color:#B91C1C;border:none;">
                        Void
                    </button>
                </div>
            </div>
        </template>
        <div x-show="items.length===0" class="text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            <p class="text-sm font-medium">Tidak ada transaksi</p>
            <p class="text-xs mt-1">Coba ubah filter atau tambah transaksi baru</p>
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
        <x-modal title="Transfer Antar Kas">
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
        <x-modal title="Void Transaksi">
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
        filterKas:'', filterDari:'', filterSampai:'',
        form:{kas_id:'',tanggal:'',tipe:'masuk',kategori_id:'',jumlah:'',keterangan:''},
        trfForm:{kas_asal_id:'',kas_tujuan_id:'',tanggal:'',jumlah:'',keterangan:''},
        get kategoriFiltered(){ return this.kategoriList.filter(k=>k.jenis===this.form.tipe); },

        async init(){
            const [kRes,katRes]=await Promise.all([apiFetch('/api/kas?per_page=100'),apiFetch('/api/kategori-transaksi?per_page=100')]);
            if(kRes?.ok){const d=await kRes.json();this.kasList=d.data||[];}
            if(katRes?.ok){const d=await katRes.json();this.kategoriList=d.data||[];}
            await this.load();
        },

        async load(){
            this.loading=true;
            let url='/api/transaksi-kas?per_page=50';
            if(this.filterKas) url+=`&kas_id=${this.filterKas}`;
            if(this.filterDari) url+=`&dari=${this.filterDari}`;
            if(this.filterSampai) url+=`&sampai=${this.filterSampai}`;
            const res=await apiFetch(url);
            if(res?.ok){const d=await res.json();this.items=d.data||[];}
            this.loading=false;
        },

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
            if(res.ok){toast('Transaksi berhasil.','success');this.open=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        async saveTransfer(){
            this.saving=true;
            const res=await apiFetch('/api/transaksi-kas/transfer',{method:'POST',body:JSON.stringify(this.trfForm)});
            const data=await res.json();
            if(res.ok){toast('Transfer berhasil.','success');this.openTrf=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        openVoid(trx){this.voidTarget=trx;this.confirmVoid=true;},

        async doVoid(){
            const res=await apiFetch(`/api/transaksi-kas/${this.voidTarget.id}`,{method:'DELETE'});
            const data=await res.json();
            if(res.ok){toast('Transaksi di-void.','success');this.confirmVoid=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
        },

        formatRp(n){ return 'Rp ' + Number(n).toLocaleString('id-ID'); }
    }
}
</script>
@endsection
