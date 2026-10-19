@extends('layouts.admin')

@section('title', 'Detail Presensi Karyawan')
@section('page-title', 'Detail Presensi')

@section('page-actions')
    <a href="{{ route('admin.attendances.index') }}" class="btn-surface btn-md flex items-center gap-1.5">
        <x-icon name="chevron-left" class="w-4 h-4" />
        <span>Kembali ke Rekap</span>
    </a>
@endsection

@section('content')
<div class="max-w-3xl flex flex-col gap-6 animate-fade-up">

    {{-- Kartu Karyawan & Status --}}
    <section class="glass-card p-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-hris-primary-soft text-hris-primary">
                <x-icon name="user" class="h-6 w-6" />
            </div>
            <div>
                <h3 class="font-bold text-base text-hris-text">{{ $attendance->user->name }}</h3>
                <p class="caption">NIP: {{ $attendance->user->nip ?? 'Tidak ada' }} · {{ $attendance->user->email }}</p>
                @if ($attendance->schedule)
                    <p class="text-xs text-hris-primary font-medium mt-0.5">
                        Jadwal: Shift {{ $attendance->schedule->shift->name }} ({{ $attendance->schedule->shift->time_range }})
                    </p>
                @endif
            </div>
        </div>

        <div>
            @if ($attendance->status === 'hadir')
                <span class="pill-success text-xs px-3 py-1.5 font-bold">Tepat Waktu</span>
            @elseif ($attendance->status === 'telat')
                <span class="pill-warning text-xs px-3 py-1.5 font-bold">Terlambat Masuk</span>
            @else
                <span class="pill-danger text-xs px-3 py-1.5 font-bold">Alpha (Tidak Hadir)</span>
            @endif
        </div>
    </section>

    {{-- Detail Clock-In & Clock-Out --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Clock-In Details --}}
        <section class="glass-card p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--bg-surface)]">
                <span class="font-bold text-xs text-hris-text flex items-center gap-1.5">
                    <x-icon name="login" class="w-4 h-4 text-hris-success" />
                    <span>Clock-In Masuk</span>
                </span>
                <span class="font-mono text-sm font-bold text-hris-text">
                    {{ $attendance->clock_in_time?->format('H:i:s') ?? '—' }} WIB
                </span>
            </div>

            <div class="text-xs flex flex-col gap-2">
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Tanggal:</span>
                    <span class="font-medium">{{ $attendance->clock_in_time?->translatedFormat('d F Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Latitude:</span>
                    <span class="font-mono">{{ $attendance->lat_in ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Longitude:</span>
                    <span class="font-mono">{{ $attendance->lng_in ?? '—' }}</span>
                </div>
            </div>

            @if ($attendance->lat_in && $attendance->lng_in)
                <div class="pt-2">
                    <a href="{{ $attendance->map_url_in }}" target="_blank" rel="noopener"
                       class="btn-primary btn-sm w-full flex items-center justify-center gap-1.5">
                        <x-icon name="map-pin" class="w-3.5 h-3.5" />
                        <span>Buka Koordinat di Google Maps</span>
                    </a>
                </div>
            @endif
        </section>

        {{-- Clock-Out Details --}}
        <section class="glass-card p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--bg-surface)]">
                <span class="font-bold text-xs text-hris-text flex items-center gap-1.5">
                    <x-icon name="logout" class="w-4 h-4 text-hris-danger" />
                    <span>Clock-Out Pulang</span>
                </span>
                <span class="font-mono text-sm font-bold text-hris-text">
                    {{ $attendance->clock_out_time?->format('H:i:s') ?? '—' }} WIB
                </span>
            </div>

            <div class="text-xs flex flex-col gap-2">
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Tanggal:</span>
                    <span class="font-medium">{{ $attendance->clock_out_time?->translatedFormat('d F Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Latitude:</span>
                    <span class="font-mono">{{ $attendance->lat_out ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Longitude:</span>
                    <span class="font-mono">{{ $attendance->lng_out ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-hris-text-muted">Durasi Kerja:</span>
                    <span class="font-bold text-hris-primary">{{ $attendance->work_duration_label ?? 'Belum Selesai' }}</span>
                </div>
            </div>

            @if ($attendance->lat_out && $attendance->lng_out)
                <div class="pt-2">
                    <a href="{{ $attendance->map_url_out }}" target="_blank" rel="noopener"
                       class="btn-surface btn-sm w-full flex items-center justify-center gap-1.5">
                        <x-icon name="map-pin" class="w-3.5 h-3.5" />
                        <span>Buka Koordinat di Google Maps</span>
                    </a>
                </div>
            @endif
        </section>

    </div>

</div>
@endsection
