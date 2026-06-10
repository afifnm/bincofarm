<!DOCTYPE html>
<html lang="id" x-data x-bind:class="$store.theme.dark ? 'dark' : ''">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Bincofarm') — Sistem Keuangan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                dark: localStorage.getItem('theme') === 'dark' ||
                      (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() {
                    this.dark = !this.dark;
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                }
            });
        });
    </script>
</head>
<body class="min-h-screen transition-colors duration-200" style="background:var(--color-bg);color:var(--color-text);">

    {{-- Toast container --}}
    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 no-print" style="pointer-events:none;"></div>

    <div class="flex min-h-screen">

        {{-- ══════════════ SIDEBAR (desktop) ══════════════ --}}
        <aside class="hidden md:flex md:flex-col w-64 shrink-0 border-r no-print"
               style="background:var(--color-sidebar); border-color:var(--color-border);">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b" style="border-color:var(--color-border);">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                     style="background:var(--color-primary);">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold leading-tight" style="color:var(--color-text);">Bincofarm</p>
                    <p class="text-xs leading-tight" style="color:var(--color-text-muted);">Sistem Keuangan</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5">
                @include('components.nav-links')
            </nav>

            {{-- User footer --}}
            <div class="px-3 py-3 border-t" style="border-color:var(--color-border);">
                <div class="flex items-center gap-3 px-2 py-2 rounded-xl" style="background:var(--color-bg);">
                    <a href="{{ route('profil') }}" class="flex items-center gap-2 flex-1 min-w-0" title="Profil">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 text-xs font-bold"
                             style="background:var(--color-primary-soft);color:var(--color-primary);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold truncate" style="color:var(--color-text);">{{ auth()->user()->name }}</p>
                            <p class="text-xs truncate" style="color:var(--color-text-muted);">{{ auth()->user()->role }}</p>
                        </div>
                    </a>
                    <button onclick="event.preventDefault();fetch('/api/logout',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(()=>location.href='/login')"
                            class="p-1 rounded-lg transition-colors"
                            style="color:var(--color-text-muted);"
                            onmouseover="this.style.color='#EF4444'"
                            onmouseout="this.style.color='var(--color-text-muted)'"
                            title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        {{-- ══════════════ MAIN AREA ══════════════ --}}
        <div class="flex-1 flex flex-col min-h-screen min-w-0">

            {{-- Topbar --}}
            <header class="topbar-glass sticky top-0 z-30 flex items-center justify-between px-4 md:px-6 border-b no-print"
                    style="height:64px; border-color:var(--color-border);">
                <div class="flex items-center gap-3">
                    {{-- Mobile brand --}}
                    <div class="flex items-center gap-2 md:hidden">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                             style="background:var(--color-primary);">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold" style="color:var(--color-text);">Bincofarm</span>
                    </div>
                    {{-- Page title (desktop) --}}
                    <h1 class="hidden md:block text-sm font-semibold" style="color:var(--color-text);">
                        @yield('title', 'Dashboard')
                    </h1>
                </div>
                <div class="flex items-center gap-1.5">
                    {{-- Dark mode toggle --}}
                    <button x-data @click="$store.theme.toggle()"
                            class="w-9 h-9 flex items-center justify-center rounded-xl transition-colors"
                            style="color:var(--color-text-muted);"
                            onmouseover="this.style.background='var(--color-bg)'"
                            onmouseout="this.style.background=''"
                            title="Toggle Dark Mode">
                        <svg x-show="!$store.theme.dark" class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg x-show="$store.theme.dark" x-cloak class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                    {{-- User badge (desktop) --}}
                    <a href="{{ route('profil') }}" class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-medium transition-colors"
                       style="border-color:var(--color-border);background:var(--color-bg);color:var(--color-text-muted);"
                       onmouseover="this.style.background='var(--color-primary-soft)';this.style.color='var(--color-primary)'"
                       onmouseout="this.style.background='var(--color-bg)';this.style.color='var(--color-text-muted)'">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold"
                             style="background:var(--color-primary-soft);color:var(--color-primary);">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-6 pb-20 md:pb-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ══════════════ BOTTOM NAV (mobile) ══════════════ --}}
    <nav class="fixed bottom-0 left-0 right-0 z-30 md:hidden border-t no-print"
         style="background:var(--color-surface); border-color:var(--color-border);">
        <div class="flex justify-around items-stretch py-1 px-1">
            @include('components.bottom-nav')
        </div>
    </nav>

    <script>
    window.apiFetch = async (url, options = {}) => {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            credentials: 'same-origin',
        };
        const res = await fetch(url, { ...defaults, ...options, headers: { ...defaults.headers, ...(options.headers || {}) } });
        if (res.status === 401) { location.href = '/login'; return; }
        return res;
    };

    window.toast = (msg, type = 'success') => {
        const c   = document.getElementById('toast-container');
        const div = document.createElement('div');
        const bg  = type === 'success' ? '#059669' : type === 'error' ? '#EF4444' : '#64748b';
        div.style.cssText = `background:${bg};color:#fff;padding:11px 16px;border-radius:12px;font-size:13px;font-weight:500;box-shadow:0 4px 20px rgba(0,0,0,.18);max-width:320px;word-break:break-word;pointer-events:auto;`;
        div.textContent = msg;
        div.className   = 'toast-enter';
        c.appendChild(div);
        setTimeout(() => {
            div.style.transition = 'opacity .3s, transform .3s';
            div.style.opacity    = '0';
            div.style.transform  = 'translateX(110%)';
            setTimeout(() => div.remove(), 320);
        }, 3400);
    };
    </script>
</body>
</html>
