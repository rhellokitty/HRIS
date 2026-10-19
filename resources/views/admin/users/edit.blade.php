@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')
@section('page-subtitle', 'Perbarui profil dan akses: {{ $user->name }}')

@section('content')
<div class="max-w-xl animate-fade-up">

    <section class="glass-card p-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="flex flex-col gap-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                       class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                       class="form-input">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $user->nip) }}"
                           class="form-input font-mono">
                    @error('nip')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="form-label">Peran / Hak Akses</label>
                    <select name="role" id="role" required class="form-input">
                        <option value="karyawan" {{ old('role', $user->role) === 'karyawan' ? 'selected' : '' }}>Karyawan (Mobile ESS)</option>
                        <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Operator (Read-Only Monitoring)</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin (Full Access Master)</option>
                    </select>
                    @error('role')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="password" class="form-label">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="password"
                       placeholder="Biarkan kosong untuk mempertahankan kata sandi lama"
                       class="form-input">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn-primary btn-md">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-surface btn-md">
                    Batal
                </a>
            </div>
        </form>
    </section>

</div>
@endsection
