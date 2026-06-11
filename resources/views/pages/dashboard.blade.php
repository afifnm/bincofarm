@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" x-init="load()" class="space-y-6">

    {{-- ── Skeleton loading ── --}}
    <div x-show="loading">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @for($i=0;$i<3;$i++)
            <div class="skeleton rounded-2xl h-32"></div>
            @endfor
        </div>
        <div class="skeleton rounded-2xl h-20 mt-4"></div>
    </div>

    {{-- ── Saldo Kas ── --}}
    <div x-show="!loading" x-cloak>

        {{-- Section header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="icon-wrap icon-wrap-emerald">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:var(--color-text);">Saldo Kas</h2>
                    <p class="text-xs" style="color:var(--color-text-muted);">Ringkasan per akun kas</p>
                </div>
            </div>
        </div>

        {{-- Kas cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="item in data.kas" :key="item.kas.id">
                <div class="stat-card">
                    <div class="flex items-start justify-between mb-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium mb-0.5" style="color:var(--color-text-muted);" x-text="item.kas.tipe_label"></p>
                            <p class="text-sm font-semibold truncate" style="color:var(--color-text);" x-text="item.kas.nama"></p>
                        </div>
                        <div class="icon-wrap ml-3 shrink-0"
                             :class="item.kas.tipe==='tunai' ? 'icon-wrap-emerald' : item.kas.tipe==='bank' ? 'icon-wrap-blue' : 'icon-wrap-purple'">
                            <svg x-show="item.kas.tipe==='tunai'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 01-.75.75h-.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                            </svg>
                            <svg x-show="item.kas.tipe==='bank'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                            </svg>
                            <svg x-show="item.kas.tipe==='ewallet'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18h3"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold mb-3" style="color:var(--color-primary);" x-text="formatRp(item.kas.saldo_berjalan)"></p>

                    <div class="flex items-center gap-4 pt-3 border-t" style="border-color:var(--color-border);">
                        <div class="flex items-center gap-1.5 text-xs">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#DCFCE7;">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="#16A34A" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                            </div>
                            <span style="color:#16A34A;font-weight:500;" x-text="formatRp(item.total_masuk)"></span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center" style="background:#FEE2E2;">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="#DC2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                            </div>
                            <span style="color:#DC2626;font-weight:500;" x-text="formatRp(item.total_keluar)"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Stok Menipis Alert ── --}}
    <div x-show="!loading && data.stok_menipis && data.stok_menipis.length > 0" x-cloak>
        <div class="flex items-center gap-2 mb-4">
            <div class="icon-wrap icon-wrap-rose">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold" style="color:var(--color-danger);">Stok Menipis</h2>
                <p class="text-xs" style="color:var(--color-text-muted);">Barang dengan stok di bawah minimum</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="w-full text-sm">
                <thead class="tbl-head">
                    <tr>
                        <th>Barang</th>
                        <th class="text-right">Stok Saat Ini</th>
                        <th class="text-right">Stok Minimum</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="b in data.stok_menipis" :key="b.id">
                        <tr class="tbl-row">
                            <td class="tbl-cell">
                                <div class="flex items-center gap-2">
                                    <div class="icon-wrap icon-wrap-rose" style="width:32px;height:32px;border-radius:8px;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-medium" style="color:var(--color-text);" x-text="b.nama"></p>
                                        <p class="text-xs" style="color:var(--color-text-muted);" x-text="b.kode"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="tbl-cell text-right">
                                <span class="badge badge-danger" x-text="b.stok + ' ' + b.satuan"></span>
                            </td>
                            <td class="tbl-cell text-right text-xs" style="color:var(--color-text-muted);" x-text="b.stok_minimum + ' ' + b.satuan"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function dashboardApp() {
    return {
        loading: true,
        data: { kas: [], stok_menipis: [], total_saldo: 0 },
        async load() {
            const res = await apiFetch('/api/dashboard');
            if (res && res.ok) this.data = await res.json();
            this.loading = false;
        },
        formatRp(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID', { minimumFractionDigits: 0 });
        }
    }
}
</script>
@endsection
