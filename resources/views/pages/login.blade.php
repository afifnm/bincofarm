<!DOCTYPE html>
<html lang="id" x-data="{ dark: false }"
      x-init="dark = localStorage.getItem('theme')==='dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme:dark)').matches)"
      x-bind:class="dark ? 'dark' : ''">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Masuk — Bincofarm</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen flex transition-colors duration-200" style="background:var(--color-bg);">

{{-- ══════ LEFT PANEL (branding) — hidden on mobile ══════ --}}
<div class="hidden md:flex flex-col items-center justify-center w-1/2 relative overflow-hidden"
     style="background:linear-gradient(155deg,#059669 0%,#047857 45%,#064E3B 100%);">

    {{-- Decorative circles --}}
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full opacity-10"
         style="background:rgba(255,255,255,.4);"></div>
    <div class="absolute -bottom-16 -right-16 w-64 h-64 rounded-full opacity-10"
         style="background:rgba(255,255,255,.3);"></div>
    <div class="absolute top-1/3 right-8 w-32 h-32 rounded-full opacity-5"
         style="background:rgba(255,255,255,.6);"></div>

    {{-- Brand content --}}
    <div class="relative z-10 text-center px-12 max-w-sm">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6"
             style="background:rgba(255,255,255,.15);backdrop-filter:blur(8px);">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-3 tracking-tight">Bincofarm</h1>
        <p class="text-emerald-100 text-sm leading-relaxed mb-8">
            Sistem Keuangan &amp; Inventori untuk manajemen bisnis pertanian yang cerdas dan terstruktur.
        </p>

        {{-- Feature pills --}}
        <div class="flex flex-col gap-3 text-left">
            @foreach(['Multi-kas &amp; transfer antar akun', 'Stok ledger real-time', 'Laporan cashflow &amp; kartu stok', 'Tutup buku per periode'] as $feat)
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl" style="background:rgba(255,255,255,.1);">
                <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0" style="background:rgba(255,255,255,.25);">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-xs text-emerald-50 font-medium">{!! $feat !!}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════ RIGHT PANEL (form) ══════ --}}
<div class="flex flex-col items-center justify-center w-full md:w-1/2 px-6 py-12">

    {{-- Mobile logo --}}
    <div class="flex items-center gap-3 mb-8 md:hidden">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:var(--color-primary);">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <div>
            <p class="font-bold text-sm" style="color:var(--color-text);">Bincofarm</p>
            <p class="text-xs" style="color:var(--color-text-muted);">Sistem Keuangan</p>
        </div>
    </div>

    {{-- Form card --}}
    <div class="w-full max-w-sm"
         x-data="{
             login: '', password: '', showPw: false, loading: false, error: '',
             async submit() {
                 this.loading = true; this.error = '';
                 const res = await fetch('/api/login', {
                     method: 'POST',
                     headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                     credentials: 'same-origin',
                     body: JSON.stringify({login: this.login, password: this.password})
                 });
                 const data = await res.json();
                 if (res.ok) { location.href = '/'; }
                 else { this.error = data.message || 'Login gagal.'; this.loading = false; }
             }
         }">

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight" style="color:var(--color-text);">Selamat datang</h2>
            <p class="text-sm mt-1" style="color:var(--color-text-muted);">Masuk untuk melanjutkan ke dashboard</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            {{-- Error alert --}}
            <div x-show="error" x-cloak
                 class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm"
                 style="background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <span x-text="error"></span>
            </div>

            {{-- Email / No. HP --}}
            <div>
                <label class="form-label">Email atau No. HP</label>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--color-text-muted);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <input type="text" x-model="login" required autocomplete="username"
                           class="form-input pl-10"
                           placeholder="email atau 08xx-xxxx-xxxx"/>
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label class="form-label">Password</label>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--color-text-muted);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                    </div>
                    <input :type="showPw ? 'text' : 'password'" x-model="password" required autocomplete="current-password"
                           class="form-input pl-10 pr-10"
                           placeholder="••••••••"/>
                    <button type="button" @click="showPw = !showPw"
                            class="absolute right-3 top-1/2 -translate-y-1/2 transition-colors"
                            style="color:var(--color-text-muted);"
                            onmouseover="this.style.color='var(--color-text)'"
                            onmouseout="this.style.color='var(--color-text-muted)'">
                        <svg x-show="!showPw" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg x-show="showPw" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" :disabled="loading"
                    class="btn btn-primary w-full justify-center mt-2"
                    :style="loading ? 'opacity:.65;cursor:not-allowed;' : ''">
                <span x-show="!loading" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    Masuk
                </span>
                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Memuat...
                </span>
            </button>
        </form>

        {{-- Dark mode toggle --}}
        <div class="mt-8 flex items-center justify-center">
            <button @click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light')"
                    class="flex items-center gap-2 text-xs transition-colors px-3 py-1.5 rounded-lg"
                    style="color:var(--color-text-muted);"
                    onmouseover="this.style.background='var(--color-border)'"
                    onmouseout="this.style.background=''">
                <svg x-show="!dark" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                </svg>
                <svg x-show="dark" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
                <span x-text="dark ? 'Mode Terang' : 'Mode Gelap'"></span>
            </button>
        </div>
    </div>
</div>

</body>
</html>
