@extends('layouts.app')
@section('title', 'Tutup Buku')

@section('content')
<div x-data="tutupBukuApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Tutup Buku Periode</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola penutupan dan pembukaan periode akuntansi</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openTutup()" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Tutup Periode
            </button>
            <button @click="openBuka()" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                Buka Terakhir
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="space-y-3">
        @for($i=0;$i<3;$i++)<div class="skeleton rounded-2xl h-24"></div>@endfor
    </div>

    {{-- Periode list --}}
    <div x-show="!loading" x-cloak class="space-y-3">
        <template x-for="periode in items" :key="periode.periode">
            <div class="table-wrap overflow-hidden">
                {{-- Periode header --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b" style="border-color:var(--color-border);"
                     :style="periode.is_closed ? 'background:var(--color-primary-soft);' : 'background:var(--color-surface);'">
                    <div class="flex items-center gap-3">
                        <div class="icon-wrap shrink-0"
                             :class="periode.is_closed ? 'icon-wrap-emerald' : 'icon-wrap-amber'"
                             style="width:36px;height:36px;border-radius:10px;">
                            <svg x-show="periode.is_closed" class="w-4.5 h-4.5" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                            <svg x-show="!periode.is_closed" x-cloak class="w-4.5 h-4.5" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color:var(--color-text);" x-text="periode.periode"></p>
                            <p class="text-xs" style="color:var(--color-text-muted);"
                               x-text="periode.is_closed ? 'Ditutup ' + (periode.closed_at ? new Date(periode.closed_at).toLocaleDateString('id-ID') : '') : 'Periode masih terbuka'"></p>
                        </div>
                    </div>
                    <span class="badge"
                          :class="periode.is_closed ? 'badge-success' : 'badge-warning'"
                          x-text="periode.is_closed ? 'Ditutup' : 'Terbuka'"></span>
                </div>

                {{-- Kas table --}}
                <table class="w-full text-xs">
                    <tbody>
                        <template x-for="k in periode.kas" :key="k.kas_id">
                            <tr class="tbl-row">
                                <td class="tbl-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full shrink-0"
                                             :style="k.is_closed ? 'background:var(--color-success);' : 'background:var(--color-warning);'"></div>
                                        <span style="color:var(--color-text-muted);" x-text="k.kas_nama"></span>
                                    </div>
                                </td>
                                <td class="tbl-cell text-right font-semibold" style="color:var(--color-text);" x-text="formatRp(k.saldo_akhir)"></td>
                                <td class="tbl-cell text-right w-20">
                                    <span class="badge"
                                          :class="k.is_closed ? 'badge-success' : 'badge-warning'"
                                          x-text="k.is_closed ? 'Ditutup' : 'Terbuka'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        <div x-show="items.length===0" class="text-center py-16" style="color:var(--color-text-muted);">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            <p class="text-sm font-medium">Belum ada periode</p>
            <p class="text-xs mt-1">Lakukan transaksi terlebih dahulu untuk membuat periode</p>
        </div>
    </div>

    {{-- Modal Tutup Periode --}}
    <div x-show="openModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Tutup Periode">
            <div class="flex items-start gap-3 p-4 rounded-xl mb-5" style="background:var(--color-bg);border:1px solid var(--color-border);">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-warning);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                <p class="text-xs" style="color:var(--color-text-muted);">
                    Pastikan semua transaksi bulan tersebut sudah benar sebelum menutup periode. Periode yang sudah ditutup tidak bisa menerima transaksi baru.
                </p>
            </div>
            <div class="mb-5">
                <label class="form-label">Periode (YYYY-MM)</label>
                <input type="month" x-model="formPeriode" class="form-input"/>
            </div>
            <div class="flex gap-3">
                <button @click="openModal=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doTutup()" :disabled="saving" class="btn btn-primary flex-1 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <span x-show="!saving">Tutup Periode</span>
                    <span x-show="saving" x-cloak>Memproses...</span>
                </button>
            </div>
        </x-modal>
    </div>

    {{-- Modal Buka Periode --}}
    <div x-show="confirmBuka" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <x-modal title="Buka Periode Terakhir">
            <div class="text-center py-2 mb-4">
                <div class="icon-wrap icon-wrap-amber mx-auto mb-4" style="width:48px;height:48px;border-radius:14px;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </div>
                <p class="text-sm font-medium mb-1" style="color:var(--color-text);">Buka kembali periode terakhir?</p>
                <p class="text-xs" style="color:var(--color-text-muted);">Hanya dapat dilakukan oleh administrator.</p>
            </div>
            <div class="flex gap-3">
                <button @click="confirmBuka=false" class="btn btn-secondary flex-1 justify-center">Batal</button>
                <button @click="doBuka()" :disabled="saving" class="btn btn-danger flex-1 justify-center">
                    <span x-show="!saving">Buka Periode</span>
                    <span x-show="saving" x-cloak>Memproses...</span>
                </button>
            </div>
        </x-modal>
    </div>
</div>

<script>
function tutupBukuApp() {
    return {
        items:[], loading:true, openModal:false, confirmBuka:false, saving:false,
        formPeriode: new Date().toISOString().slice(0,7),

        async load(){
            this.loading=true;
            const res=await apiFetch('/api/periode');
            if(res?.ok) this.items=await res.json();
            this.loading=false;
        },
        openTutup(){ this.openModal=true; },
        openBuka(){ this.confirmBuka=true; },

        async doTutup(){
            this.saving=true;
            const res=await apiFetch('/api/periode/tutup',{method:'POST',body:JSON.stringify({periode:this.formPeriode})});
            const data=await res.json();
            if(res.ok){toast('Periode ditutup.','success');this.openModal=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },
        async doBuka(){
            this.saving=true;
            const res=await apiFetch('/api/periode/buka',{method:'POST'});
            const data=await res.json();
            if(res.ok){toast('Periode dibuka.','success');this.confirmBuka=false;await this.load();}
            else{toast(data.message||'Gagal.','error');}
            this.saving=false;
        },
        formatRp(n){ return 'Rp '+Number(n||0).toLocaleString('id-ID'); }
    }
}
</script>
@endsection
