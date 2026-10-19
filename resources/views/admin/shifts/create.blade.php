@extends('layouts.admin')

@section('title', 'Tambah Master Shift')
@section('page-title', 'Tambah Master Shift')
@section('page-subtitle', 'Definisikan jam kerja baru')

@section('content')
<div class="max-w-xl animate-fade-up">

    <section class="glass-card p-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('admin.shifts.store') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="name" class="form-label">Nama Shift</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                       placeholder="Contoh: Shift Pagi, Shift Malam"
                       class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="form-label">Jam Mulai (WIB)</label>
                    <input type="time" name="start_time" id="start_time" required
                           value="{{ old('start_time', '08:00') }}"
                           class="form-input">
                    @error('start_time')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="form-label">Jam Selesai (WIB)</label>
                    <input type="time" name="end_time" id="end_time" required
                           value="{{ old('end_time', '16:00') }}"
                           class="form-input">
                    @error('end_time')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <p class="caption text-[11px]">
                Tip: Jika jam selesai lebih kecil dari jam mulai (misal 21:00 - 05:00), sistem otomatis mengenalinya sebagai shift malam lintas hari.
            </p>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn-primary btn-md">
                    Simpan Shift
                </button>
                <a href="{{ route('admin.shifts.index') }}" class="btn-surface btn-md">
                    Batal
                </a>
            </div>
        </form>
    </section>

</div>
@endsection
