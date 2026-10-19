@extends('layouts.employee')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="flex flex-col gap-4 animate-fade-up">

    <div class="flex items-center justify-between px-1">
        <h2 class="page-title">Riwayat Absensi</h2>
        <a href="{{ route('employee.attendance.index') }}" class="btn-surface btn-sm">
            Presensi Hari Ini
        </a>
    </div>

    @if ($attendances->isEmpty())
        <div class="glass-card p-8 text-center flex flex-col items-center gap-2">
            <x-icon name="clipboard" class="w-10 h-10 text-hris-text-muted" />
            <p class="font-semibold text-xs text-hris-text">Belum ada riwayat absensi</p>
            <p class="caption">Data kehadiran Anda akan dicatat di sini.</p>
        </div>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($attendances as $att)
                <div class="glass-card p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="font-bold text-sm text-hris-text">
                                {{ $att->clock_in_time ? $att->clock_in_time->translatedFormat('l, d M Y') : 'Tanpa Clock-in' }}
                            </span>
                            @if ($att->schedule)
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <x-shift-badge :shift="$att->schedule->shift" />
                                    <span class="text-[10px] text-hris-text-muted font-mono">{{ $att->schedule->shift->time_range }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($att->status === 'hadir')
                            <span class="pill-success">Tepat Waktu</span>
                        @elseif ($att->status === 'telat')
                            <span class="pill-warning">Terlambat</span>
                        @else
                            <span class="pill-danger">Alpha</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-[var(--bg-surface)]">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-hris-text-muted">Clock-In</span>
                            <span class="font-mono font-semibold">{{ $att->clock_in_time?->format('H:i:s') ?? '-' }}</span>
                            @if ($att->map_url_in)
                                <a href="{{ $att->map_url_in }}" target="_blank" class="text-[10px] text-hris-info hover:underline inline-flex items-center gap-0.5 mt-0.5">
                                    <x-icon name="map-pin" class="w-3 h-3" />
                                    <span>Peta GPS</span>
                                </a>
                            @endif
                        </div>

                        <div class="flex flex-col">
                            <span class="text-[10px] text-hris-text-muted">Clock-Out</span>
                            <span class="font-mono font-semibold">{{ $att->clock_out_time?->format('H:i:s') ?? '-' }}</span>
                            @if ($att->map_url_out)
                                <a href="{{ $att->map_url_out }}" target="_blank" class="text-[10px] text-hris-info hover:underline inline-flex items-center gap-0.5 mt-0.5">
                                    <x-icon name="map-pin" class="w-3 h-3" />
                                    <span>Peta GPS</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($att->work_duration_label)
                        <div class="text-[10px] text-hris-text-secondary bg-hris-surface px-2.5 py-1 rounded-md">
                            Total Jam Kerja: <strong>{{ $att->work_duration_label }}</strong>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $attendances->links() }}
        </div>
    @endif

</div>
@endsection
