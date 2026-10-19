@extends('layouts.employee')

@section('title', 'Ajukan Cuti / Izin')

@section('content')
<div class="flex flex-col gap-4 animate-fade-up">

    <div class="flex items-center justify-between px-1">
        <h2 class="page-title">Formulir Cuti / Izin</h2>
        <a href="{{ route('employee.leave.index') }}" class="btn-surface btn-sm">
            Riwayat
        </a>
    </div>

    <section class="glass-card p-5 sm:p-6 flex flex-col gap-4">
        <form method="POST" action="{{ route('employee.leave.store') }}" class="flex flex-col gap-4">
            @csrf

            {{-- Jenis Pengajuan --}}
            <div>
                <label for="type" class="form-label">Jenis Pengajuan</label>
                <select name="type" id="type" required class="form-input">
                    <option value="">Pilih jenis...</option>
                    @foreach (\App\Models\LeaveRequest::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Mulai & Selesai --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" required
                           value="{{ old('start_date', now()->toDateString()) }}"
                           min="{{ now()->toDateString() }}"
                           class="form-input">
                    @error('start_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" required
                           value="{{ old('end_date', now()->toDateString()) }}"
                           min="{{ now()->toDateString() }}"
                           class="form-input">
                    @error('end_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Alasan / Keterangan --}}
            <div>
                <label for="reason" class="form-label">Keterangan / Alasan</label>
                <textarea name="reason" id="reason" rows="4" required
                          placeholder="Jelaskan keperluan cuti, izin, atau kondisi kesehatan Anda..."
                          class="form-input">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <button type="submit" class="btn-primary btn-lg w-full font-semibold">
                    Kirim Pengajuan
                </button>
                <a href="{{ route('employee.home') }}" class="btn-ghost btn-sm w-full text-center">
                    Batal
                </a>
            </div>
        </form>
    </section>

</div>
@endsection
