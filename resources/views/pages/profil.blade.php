@extends('layouts.app')
@section('title', 'Profil Pengguna')

@section('content')
<div x-data="profilApp()" x-init="load()">

    {{-- Page header --}}
    <div class="page-header">
        <div>
            <h2 class="text-lg font-semibold tracking-tight" style="color:var(--color-text);">Profil Pengguna</h2>
            <p class="text-xs mt-0.5" style="color:var(--color-text-muted);">Kelola informasi akun dan keamanan Anda</p>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 max-w-3xl">

        {{-- Profile Card --}}
        <div class="card card-p">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold shrink-0"
                     style="background:var(--color-primary-soft);color:var(--color-primary);"
                     x-text="user?.name ? user.name[0].toUpperCase() : '?'">
                </div>
                <div>
                    <p class="font-semibold" style="color:var(--color-text);" x-text="user?.name || '—'"></p>
                    <p class="text-xs mt-0.5" style="color:var(--color-text-muted);" x-text="user?.email || '—'"></p>
                    <span class="badge badge-info mt-1" x-text="user?.role || '—'"></span>
                </div>
            </div>

            <form @submit.prevent="saveProfile" class="space-y-4">
                <h4 class="text-xs font-semibold uppercase tracking-wider" style="color:var(--color-text-muted);">Informasi Profil</h4>
                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" x-model="form.name" required class="form-input" placeholder="Nama Anda"/>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" :value="user?.email" disabled class="form-input opacity-60 cursor-not-allowed"/>
                    <p class="text-xs mt-1" style="color:var(--color-text-muted);">Email tidak dapat diubah</p>
                </div>
                <div>
                    <label class="form-label">Nomor HP</label>
                    <input type="text" x-model="form.phone" class="form-input" placeholder="Opsional"/>
                </div>
                <button type="submit" :disabled="savingProfile" class="btn btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-show="!savingProfile">Simpan Profil</span>
                    <span x-show="savingProfile" x-cloak>Menyimpan...</span>
                </button>
            </form>
        </div>

        {{-- Password Card --}}
        <div class="card card-p">
            <h4 class="text-xs font-semibold uppercase tracking-wider mb-4" style="color:var(--color-text-muted);">Ubah Password</h4>
            <form @submit.prevent="savePassword" class="space-y-4">
                <div>
                    <label class="form-label">Password Lama</label>
                    <div class="relative">
                        <input :type="showOld ? 'text' : 'password'" x-model="pwForm.current_password" required class="form-input pr-10" placeholder="••••••••"/>
                        <button type="button" @click="showOld=!showOld"
                                class="absolute right-3 top-1/2 -translate-y-1/2"
                                style="color:var(--color-text-muted);">
                            <svg x-show="!showOld" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="showOld" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="form-label">Password Baru</label>
                    <input :type="showNew ? 'text' : 'password'" x-model="pwForm.password" required class="form-input" placeholder="Min. 6 karakter"/>
                    <button type="button" @click="showNew=!showNew" class="text-xs mt-1" style="color:var(--color-primary);">
                        <span x-show="!showNew">Tampilkan</span>
                        <span x-show="showNew" x-cloak>Sembunyikan</span>
                    </button>
                </div>
                <div>
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input :type="showNew ? 'text' : 'password'" x-model="pwForm.password_confirmation" required class="form-input" placeholder="Ulangi password baru"/>
                </div>
                <button type="submit" :disabled="savingPw" class="btn btn-secondary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    <span x-show="!savingPw">Ubah Password</span>
                    <span x-show="savingPw" x-cloak>Memproses...</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function profilApp() {
    return {
        user: null,
        form: { name: '', phone: '' },
        pwForm: { current_password: '', password: '', password_confirmation: '' },
        savingProfile: false, savingPw: false,
        showOld: false, showNew: false,

        async load() {
            const res = await apiFetch('/api/user/profile');
            if (res?.ok) {
                const d = await res.json();
                this.user = d.user;
                this.form.name = this.user.name || '';
                this.form.phone = this.user.phone || '';
            }
        },

        async saveProfile() {
            this.savingProfile = true;
            const res = await apiFetch('/api/user/profile', { method: 'PUT', body: JSON.stringify(this.form) });
            const d = await res.json();
            if (res.ok) {
                this.user = d.user;
                toast('Profil berhasil disimpan.', 'success');
            } else {
                const msg = d.errors ? Object.values(d.errors).flat().join(' ') : d.message;
                toast(msg, 'error');
            }
            this.savingProfile = false;
        },

        async savePassword() {
            this.savingPw = true;
            const res = await apiFetch('/api/user/password', { method: 'PUT', body: JSON.stringify(this.pwForm) });
            const d = await res.json();
            if (res.ok) {
                toast('Password berhasil diubah.', 'success');
                this.pwForm = { current_password: '', password: '', password_confirmation: '' };
            } else {
                const msg = d.errors ? Object.values(d.errors).flat().join(' ') : d.message;
                toast(msg, 'error');
            }
            this.savingPw = false;
        }
    }
}
</script>
@endsection
