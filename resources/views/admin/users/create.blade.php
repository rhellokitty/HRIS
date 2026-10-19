@extends('layouts.admin')

@section('title', 'Tambah Pengguna Baru')
@section('page-title', 'Tambah Pengguna')

@section('content')
<div class="max-w-xl animate-fade-up">

    <section class="glass-card p-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('admin.users.store') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Muhammad Raja Daffa"
                       class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                       placeholder="nama@perusahaan.com"
                       class="form-input">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="nip" class="form-label">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                           placeholder="Contoh: 20260819"
                           class="form-input font-mono">
                    @error('nip')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="form-label">Peran / Hak Akses</label>
                    <select name="role" id="role" required class="form-input">
                        <option value="karyawan" {{ old('role', 'karyawan') === 'karyawan' ? 'selected' : '' }}>Karyawan (Mobile ESS)</option>
                        <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Operator (Read-Only Monitoring)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Full Access Master)</option>
                    </select>
                    @error('role')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="password" class="form-label">Kata Sandi Default</label>
                <input type="password" name="password" id="password" required
                       placeholder="Minimal 8 karakter"
                       class="form-input">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn-primary btn-md flex items-center gap-1.5">
                    <x-icon name="check" class="w-4 h-4" />
                    <span>Daftarkan Akun</span>
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-surface btn-md flex items-center gap-1.5">
                    <x-icon name="x" class="w-4 h-4" />
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </section>

</div>
@endsection
