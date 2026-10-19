@extends('layouts.employee')

@section('title', 'Akun Pengguna')

@section('content')
<div class="flex flex-col gap-4 animate-fade-up">

    <h2 class="page-title px-1">Akun Saya</h2>

    {{-- Detail Profil --}}
    <section class="glass-card p-5 flex flex-col items-center text-center gap-3">
        <div class="avatar w-16 h-16 text-xl">
            {{ $user->initials }}
        </div>

        <div>
            <h3 class="font-bold text-base text-hris-text">{{ $user->name }}</h3>
            <p class="caption">{{ $user->email }}</p>
        </div>

        <div class="w-full flex justify-around pt-3 border-t border-[var(--bg-surface)] text-xs">
            <div class="flex flex-col">
                <span class="text-micro text-hris-text-muted">NIP</span>
                <span class="font-mono font-bold text-hris-text">{{ $user->nip ?? '-' }}</span>
            </div>
            <div class="flex flex-col">
                <span class="text-micro text-hris-text-muted">Peran</span>
                <span class="font-bold text-hris-primary capitalize">{{ $user->role }}</span>
            </div>
        </div>
    </section>

    {{-- Pengaturan Tampilan Tema --}}
    <section class="glass-card p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-md bg-hris-surface flex items-center justify-center text-hris-text">
                <x-icon name="moon" class="w-4 h-4" />
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-xs text-hris-text">Mode Tampilan</span>
                <span class="text-[10px] text-hris-text-muted">Ganti tema gelap / terang</span>
            </div>
        </div>
        <button type="button" data-theme-toggle class="btn-surface btn-sm flex items-center gap-1.5">
            <x-icon name="sun" class="w-3.5 h-3.5" />
            <span data-theme-label>Gelap</span>
        </button>
    </section>

    {{-- Ganti Kata Sandi --}}
    <section class="glass-card p-5 flex flex-col gap-3">
        <h3 class="section-title">Ganti Kata Sandi</h3>

        <form method="POST" action="{{ route('employee.profile.password') }}" class="flex flex-col gap-3">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                <input type="password" name="current_password" id="current_password" required class="form-input">
                @error('current_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="form-label">Kata Sandi Baru</label>
                <input type="password" name="password" id="password" required class="form-input">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="form-input">
            </div>

            <button type="submit" class="btn-primary btn-md w-full mt-1">
                Perbarui Kata Sandi
            </button>
        </form>
    </section>

    {{-- Tombol Logout --}}
    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <button type="submit" class="btn-surface btn-lg w-full text-hris-danger flex items-center justify-center gap-2">
            <x-icon name="logout" class="w-4 h-4" />
            <span>Keluar dari Aplikasi</span>
        </button>
    </form>

</div>
@endsection
