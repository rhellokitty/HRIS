@extends('layouts.admin')

@section('title', 'Edit Master Shift')
@section('page-title', 'Edit Master Shift')

@section('content')
<div class="max-w-xl animate-fade-up">

    <section class="glass-card p-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('admin.shifts.update', $shift) }}" class="flex flex-col gap-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="form-label">Nama Shift</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $shift->name) }}"
                       class="form-input">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="form-label">Jam Mulai (WIB)</label>
                    <input type="time" name="start_time" id="start_time" required
                           value="{{ old('start_time', $shift->start_label) }}"
                           class="form-input">
                    @error('start_time')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="form-label">Jam Selesai (WIB)</label>
                    <input type="time" name="end_time" id="end_time" required
                           value="{{ old('end_time', $shift->end_label) }}"
                           class="form-input">
                    @error('end_time')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn-primary btn-md flex items-center gap-1.5">
                    <x-icon name="check" class="w-4 h-4" />
                    <span>Simpan Perubahan</span>
                </button>
                <a href="{{ route('admin.shifts.index') }}" class="btn-surface btn-md flex items-center gap-1.5">
                    <x-icon name="x" class="w-4 h-4" />
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </section>

</div>
@endsection
