@extends('layouts.app')
@section('title', 'Log Sistem')

@section('content')
<div x-data="logApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Log Aktivitas Sistem</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Rekam jejak semua aktivitas dan transaksi pengguna</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 p-4 rounded-xl border" style="background:var(--color-surface);border-color:var(--color-border);">
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-40">
                <label class="form-label">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--color-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" x-model="search" @input.debounce.300ms="load()" placeholder="Cari deskripsi / aksi..." class="form-input pl-9"/>
                </div>
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
        {{-- Date shortcuts --}}
        <div class="flex flex-wrap gap-1.5 mt-3">
            <button type="button" @click="setThisMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Bulan Ini
            </button>
            <button type="button" @click="setLastMonth()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                Bulan Lalu
            </button>
            <button type="button" @click="resetDates()" class="filter-pill text-xs px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset
            </button>
        </div>
    </div>

    {{-- Loading skeleton --}}
    <div x-show="loading" class="space-y-2">
        @for($i=0;$i<8;$i++)<div class="skeleton rounded-xl h-12"></div>@endfor
    </div>

    {{-- Table --}}
    <div x-show="!loading" x-cloak class="table-wrap">
        <table class="w-full text-sm">
            <thead class="tbl-head">
                <tr>
                    <th class="w-10 text-center">#</th>
                    <th>Aktivitas</th>
                    <th class="hidden md:table-cell text-center">Aksi</th>
                    <th class="hidden lg:table-cell">User</th>
                    <th class="hidden md:table-cell">Waktu</th>
                    <th class="hidden lg:table-cell">IP</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(log, idx) in items" :key="log.id">
                    <tr class="tbl-row">
                        <td class="tbl-cell text-center text-xs" style="color:var(--color-text-muted);"
                            x-text="(meta.from || 1) + idx"></td>
                        <td class="tbl-cell">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                     :class="getActionClass(log.action)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         x-html="getActionIcon(log.action)"></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium" style="color:var(--color-text);" x-text="log.description"></p>
                                    <p class="text-xs md:hidden" style="color:var(--color-text-muted);" x-text="formatTime(log.created_at)"></p>
                                </div>
                            </div>
                        </td>
                        <td class="tbl-cell hidden md:table-cell text-center">
                            <span class="badge" :class="getActionBadge(log.action)" x-text="log.action"></span>
                        </td>
                        <td class="tbl-cell hidden lg:table-cell text-xs" style="color:var(--color-text-muted);" x-text="log.user?.name || '—'"></td>
                        <td class="tbl-cell hidden md:table-cell text-xs" style="color:var(--color-text-muted);" x-text="formatTime(log.created_at)"></td>
                        <td class="tbl-cell hidden lg:table-cell text-xs font-mono" style="color:var(--color-text-muted);" x-text="log.ip_address || '—'"></td>
                    </tr>
                </template>
                <tr x-show="!loading && items.length===0">
                    <td colspan="6" class="tbl-cell text-center py-12" style="color:var(--color-text-muted);">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        <p class="text-sm font-medium">Belum ada log aktivitas</p>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:var(--color-border);">
            <p class="text-xs" style="color:var(--color-text-muted);">
                <span x-text="meta.from"></span>–<span x-text="meta.to"></span> dari <span x-text="meta.total"></span> log
            </p>
            <div class="flex gap-1">
                <button @click="goPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageRange" :key="p">
                    <button @click="goPage(p)" :disabled="p === '...'"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs"
                            :style="p === meta.current_page ? 'background:var(--color-primary);color:#fff;' : 'border:1px solid var(--color-border);'"
                            x-text="p"></button>
                </template>
                <button @click="goPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs disabled:opacity-40"
                        style="border:1px solid var(--color-border);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function logApp() {
    return {
        items:[], loading:true,
        search:'', filterDari:'', filterSampai:'',
        meta:{ current_page:1, last_page:1, from:1, to:1, total:0 },

        get pageRange() {
            const cur=this.meta.current_page, last=this.meta.last_page;
            if(last<=7) return Array.from({length:last},(_,i)=>i+1);
            const pages=[];
            if(cur<=4){for(let i=1;i<=5;i++) pages.push(i);pages.push('...');pages.push(last);}
            else if(cur>=last-3){pages.push(1);pages.push('...');for(let i=last-4;i<=last;i++) pages.push(i);}
            else{pages.push(1);pages.push('...');pages.push(cur-1);pages.push(cur);pages.push(cur+1);pages.push('...');pages.push(last);}
            return pages;
        },

        async load(page=1){
            this.loading=true;
            let url=`/api/activity-log?page=${page}&per_page=25`;
            if(this.search) url+=`&search=${encodeURIComponent(this.search)}`;
            if(this.filterDari) url+=`&dari=${this.filterDari}`;
            if(this.filterSampai) url+=`&sampai=${this.filterSampai}`;
            const res=await apiFetch(url);
            if(res?.ok){
                const d=await res.json();
                this.items=d.data||[];
                this.meta=d.meta||{current_page:1,last_page:1,from:1,to:this.items.length,total:this.items.length};
            }
            this.loading=false;
        },

        goPage(p){ if(p!=='...'&&p>=1&&p<=this.meta.last_page) this.load(p); },

        setThisMonth(){ const n=new Date(); this.filterDari=new Date(n.getFullYear(),n.getMonth(),1).toISOString().slice(0,10); this.filterSampai=n.toISOString().slice(0,10); this.load(); },
        setLastMonth(){ const n=new Date(); const y=n.getMonth()===0?n.getFullYear()-1:n.getFullYear(); const m=n.getMonth()===0?11:n.getMonth()-1; this.filterDari=new Date(y,m,1).toISOString().slice(0,10); this.filterSampai=new Date(y,m+1,0).toISOString().slice(0,10); this.load(); },
        resetDates(){ this.filterDari=''; this.filterSampai=''; this.load(); },

        getActionClass(action){
            const map={login:'icon-wrap-emerald',logout:'icon-wrap-amber',create:'icon-wrap-blue',update:'icon-wrap-blue',update_profile:'icon-wrap-blue',change_password:'icon-wrap-purple',delete:'icon-wrap-rose',void:'icon-wrap-rose',transfer:'icon-wrap-purple'};
            return map[action]||'icon-wrap-emerald';
        },
        getActionBadge(action){
            const map={login:'badge-success',logout:'badge-warning',create:'badge-info',update:'badge-info',update_profile:'badge-info',change_password:'badge-info',delete:'badge-danger',void:'badge-danger',transfer:'badge-neutral'};
            return map[action]||'badge-neutral';
        },
        getActionIcon(action){
            if(['login','create'].includes(action)) return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>';
            if(['logout','void','delete'].includes(action)) return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636"/>';
            if(action==='transfer') return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>';
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>';
        },
        formatTime(dt){
            if(!dt) return '—';
            const d=new Date(dt);
            return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})+' '+d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
        }
    }
}
</script>
@endsection
