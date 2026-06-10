@extends('layouts.app')
@section('title', 'Mutasi Barang')

@section('content')
<div x-data="mutasiApp()" x-init="init()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Mutasi Barang</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Pencatatan keluar masuk dan penyesuaian stok</p>
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
            <button @click="openCreate('penyesuaian')" class="btn btn-sm btn-secondary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                Sesuaian
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2 mb-5 p-3 rounded-xl border" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex-1 min-w-32">
            <label class="form-label">Barang</label>
            <select x-model="filterBarang" @change="load()" class="form-input">
                <option value="">Semua Barang</option>
                <template x-for="b in barangList" :key="b.id"><option :value="b.id" x-text="b.nama"></option></template>
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
        @for($i=0;$i<4;$i++)<div class="skeleton rounded-2xl h-16"></div>@endfor
    </div>

    {{-- Mutation list --}}
    <div x-show="!loading" x-cloak class="space-y-2">
        <template x-for="m in items" :key="m.id">
            <div class="card card-p flex items-center justify-between gap-3"
                 :class="m.is_void ? 'void-row' : ''">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         :class="m.tipe==='masuk' ? 'icon-wrap-emerald' : m.tipe==='keluar' ? 'icon-wrap-rose' : 'icon-wrap-blue'">
                        <svg x-show="m.tipe==='masuk'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                        <svg x-show="m.tipe==='keluar'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                        <svg x-show="m.tipe==='penyesuaian'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate" style="color:var(--color-text);" x-text="m.nomor"></p>
                        <p class="text-xs truncate mt-0.5" style="color:var(--color-text-muted);"
                           x-text="(m.barang?.nama||'-') + (m.keterangan?' — '+m.keterangan:'')"></p>
                        <p class="text-xs mt-0.5" style="color:var(--color-text-muted);"
                           x-text="m.tanggal + ' · stok setelah: ' + m.stok_setelah + ' ' + (m.barang?.satuan||'')"></p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold"
                       :style="m.tipe==='masuk'?'color:var(--color-success);':m.tipe==='keluar'?'color:var(--color-danger);':'color:#0369A1;'"
                       x-text="(m.tipe==='masuk'?'+':m.tipe==='keluar'?'-':'±') + m.qty + ' ' + (m.barang?.satuan||'')">
                    </p>
                    <span x-show="m.is_void" class="badge badge-danger mt-1">VOID</span>
                    <button x-show="!m.is_void" @click="openVoid(m)"
                            class="btn btn-sm mt-1" style="background:#FEE2E2;color:#B91C1C;border:none;">
                        Void
                    </button>
                </div>
            </div>
        </template>
        <div x-show="items.length===0" class="text-center py-12" style="color:var(--color-text-muted);">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
            <p class="text-sm font-medium">Tidak ada mutasi</p>
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal>
            <h3 class="text-base font-semibold mb-5" style="color:var(--color-text);"
                x-text="form.tipe==='masuk'?'Barang Masuk':form.tipe==='keluar'?'Barang Keluar':'Penyesuaian Stok'"></h3>
            <form @submit.prevent="save" class="space-y-4">
                <div x-show="periodeClosed" x-cloak
                     class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-xs"
                     style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    Periode ini sudah ditutup.
                </div>
                <div>
                    <label class="form-label">Barang</label>
                    <select x-model="form.barang_id" required class="form-input">
                        <template x-for="b in barangList" :key="b.id">
                            <option :value="b.id" x-text="b.nama + ' (stok: ' + b.stok + ' ' + b.satuan + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal</label>
                    <input type="date" x-model="form.tanggal" @change="checkPeriode()" required class="form-input"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label" x-text="form.tipe==='penyesuaian'?'Stok Baru':'Qty'"></label>
                        <input type="number" x-model="form.qty" min="0.01" step="0.01" required class="form-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="form-label">Harga Satuan (Rp)</label>
                        <input type="number" x-model="form.harga_satuan" min="0" step="0.01" class="form-input" placeholder="0"/>
                    </div>
                </div>
                <div>
                    <label class="form-label">Referensi (No. Faktur)</label>
                    <input type="text" x-model="form.referensi" class="form-input" placeholder="Opsional"/>
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input type="text" x-model="form.keterangan" class="form-input" placeholder="Opsional"/>
                </div>
                {{-- Link to Kas --}}
                <div x-show="form.tipe!=='penyesuaian'" class="border-t pt-4" style="border-color:var(--color-border);">
                    <div class="flex items-center gap-2 mb-3">
                        <input type="checkbox" x-model="linkKas" id="link_kas" class="w-4 h-4 rounded accent-emerald-600 cursor-pointer"/>
                        <label for="link_kas" class="text-sm cursor-pointer" style="color:var(--color-text);">
                            Buat transaksi kas otomatis
                        </label>
                    </div>
                    <div x-show="linkKas" x-cloak>
                        <label class="form-label">Pilih Kas</label>
                        <select x-model="form.kas_id" class="form-input">
                            <option value="">— Pilih Kas —</option>
                            <template x-for="k in kasList" :key="k.id"><option :value="k.id" x-text="k.nama"></option></template>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="open=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                    <button type="submit" :disabled="saving" class="btn flex-1 justify-center text-white"
                            :style="form.tipe==='masuk'?'background:var(--color-success);':form.tipe==='keluar'?'background:var(--color-danger);':'background:#0369A1;'">
                        <span x-show="!saving">Simpan</span>
                        <span x-show="saving" x-cloak>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Modal Void --}}
    <div x-show="confirmVoid" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Void Mutasi">
            <p class="text-sm mb-5" style="color:var(--color-text);">
                Void mutasi <strong x-text="voidTarget?.nomor"></strong>? Stok akan dikembalikan.
            </p>
            <div class="flex gap-3">
                <button @click="confirmVoid=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doVoid()" class="btn btn-danger flex-1 justify-center">Void</button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function mutasiApp() {
    return {
        items:[], loading:true, open:false, saving:false,
        confirmVoid:false, voidTarget:null, periodeClosed:false, linkKas:false,
        barangList:[], kasList:[],
        filterBarang:'', filterDari:'', filterSampai:'',
        form:{barang_id:'',tanggal:'',tipe:'masuk',qty:'',harga_satuan:0,referensi:'',keterangan:'',kas_id:''},

        async init(){
            const [bRes,kRes]=await Promise.all([apiFetch('/api/barang?per_page=100'),apiFetch('/api/kas?per_page=100')]);
            if(bRes?.ok){const d=await bRes.json();this.barangList=d.data||[];}
            if(kRes?.ok){const d=await kRes.json();this.kasList=d.data||[];}
            await this.load();
        },

        async load(){
            this.loading=true;
            let url='/api/mutasi-barang?per_page=50';
            if(this.filterBarang) url+=`&barang_id=${this.filterBarang}`;
            if(this.filterDari) url+=`&dari=${this.filterDari}`;
            if(this.filterSampai) url+=`&sampai=${this.filterSampai}`;
            const res=await apiFetch(url);
            if(res?.ok){const d=await res.json();this.items=d.data||[];}
            this.loading=false;
        },

        openCreate(tipe){
            this.form={barang_id:this.barangList[0]?.id||'',tanggal:new Date().toISOString().slice(0,10),tipe,qty:'',harga_satuan:0,referensi:'',keterangan:'',kas_id:''};
            this.linkKas=false; this.periodeClosed=false; this.open=true;
        },

        async checkPeriode(){
            if(!this.form.tanggal||!this.form.barang_id) return;
            const res=await apiFetch(`/api/periode/check?tanggal=${this.form.tanggal}`);
            if(res?.ok){const d=await res.json();this.periodeClosed=d.is_closed;}
        },

        async save(){
            this.saving=true;
            const payload={...this.form};
            if(!this.linkKas) delete payload.kas_id;
            const res=await apiFetch('/api/mutasi-barang',{method:'POST',body:JSON.stringify(payload)});
            const data=await res.json();
            if(res.ok){toast('Mutasi berhasil.','success');this.open=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },

        openVoid(m){this.voidTarget=m;this.confirmVoid=true;},

        async doVoid(){
            const res=await apiFetch(`/api/mutasi-barang/${this.voidTarget.id}`,{method:'DELETE'});
            const data=await res.json();
            if(res.ok){toast('Mutasi di-void.','success');this.confirmVoid=false;await this.load();}
            else{toast(data.message,'error');}
        }
    }
}
</script>
@endsection
