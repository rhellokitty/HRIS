@extends('layouts.guest')

@section('title', 'Masuk ke Sistem')

@section('content')
<section class="glass-panel flex flex-col gap-5 p-6 sm:p-8 animate-fade-up">
    <div>
        <h2 class="page-title text-center">Masuk HRIS</h2>
        <p class="caption text-center mt-1">Gunakan Email atau NIP untuk mengakses portal</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="flex flex-col gap-4">
        @csrf

        <div>
            <label for="login" class="form-label">Email atau NIP</label>
            <div class="relative">
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus
                       placeholder="nama@perusahaan.com atau NIP"
                       class="form-input" autocomplete="username">
            </div>
            @error('login')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">Kata Sandi</label>
            <input id="password" type="password" name="password" required
                   placeholder="••••••••"
                   class="form-input" autocomplete="current-password">
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-xs text-hris-text-secondary">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="rounded bg-hris-surface text-hris-primary focus:ring-0 border-none">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="btn-primary btn-lg w-full mt-1">
            Masuk Portal
        </button>
    </form>

    {{-- Akses Cepat untuk Pengujian / Demo --}}
    <div class="pt-4 border-t border-[var(--bg-surface)] flex flex-col gap-2">
        <p class="text-micro uppercase text-hris-text-muted text-center">Akun Demo (Klik untuk Isi Cepat)</p>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" class="btn-surface btn-sm flex flex-col py-2"
                    onclick="fillLogin('admin@hris.local', 'password')">
                <span class="font-bold text-hris-primary">Admin</span>
                <span class="text-[9px] text-hris-text-muted">Full Access</span>
            </button>
            <button type="button" class="btn-surface btn-sm flex flex-col py-2"
                    onclick="fillLogin('operator@hris.local', 'password')">
                <span class="font-bold text-hris-info">Operator</span>
                <span class="text-[9px] text-hris-text-muted">Read-Only</span>
            </button>
            <button type="button" class="btn-surface btn-sm flex flex-col py-2"
                    onclick="fillLogin('daffa@hris.local', 'password')">
                <span class="font-bold text-hris-success">Karyawan</span>
                <span class="text-[9px] text-hris-text-muted">Mobile ESS</span>
            </button>
        </div>
    </div>
</section>

@push('scripts')
<script>
    function fillLogin(email, pass) {
        document.getElementById('login').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endpush
@endsection
