@extends('layouts.employee')

@section('title', 'Jadwal Kerja')

@section('content')
<div class="flex flex-col gap-4 animate-fade-up">

    <div class="flex items-center justify-between px-1">
        <div>
            <h2 class="page-title">Jadwal Shift</h2>
            <p class="caption">Periode 2 Pekan (Pekan ini &amp; Pekan depan)</p>
        </div>
        <a href="{{ route('employee.attendance.index') }}" class="btn-primary btn-sm">
            Presensi
        </a>
    </div>

    @if ($schedules->isEmpty())
        <div class="glass-card p-8 text-center flex flex-col items-center gap-2">
            <x-icon name="calendar" class="w-10 h-10 text-hris-text-muted" />
            <p class="font-semibold text-xs text-hris-text">Belum ada plotting jadwal</p>
            <p class="caption">Jadwal shift kerja Anda belum digenerate oleh Admin.</p>
        </div>
    @else
        <div class="flex flex-col gap-2.5">
            @foreach ($schedules as $item)
                @php
                    $isToday = $item->date->isToday();
                    $isPast = $item->date->isPast() && ! $isToday;
                @endphp
                <div @class([
                    'glass-card p-3.5 flex items-center justify-between text-xs transition-all',
                    'ring-2 ring-hris-primary/50' => $isToday,
                    'opacity-70' => $isPast,
                ])>
                    <div class="flex items-center gap-3">
                        <div @class([
                            'w-10 h-10 rounded-md flex flex-col items-center justify-center text-center font-bold',
                            'bg-hris-primary text-white' => $isToday,
                            'bg-hris-surface text-hris-text' => ! $isToday,
                        ])>
                            <span class="text-[9px] uppercase leading-none">{{ $item->date->translatedFormat('D') }}</span>
                            <span class="text-sm leading-none mt-0.5">{{ $item->date->format('d') }}</span>
                        </div>

                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-hris-text">{{ $item->shift->name }}</span>
                                @if ($isToday)
                                    <span class="pill-success text-[9px] px-1.5 py-0.5">Hari Ini</span>
                                @endif
                            </div>
                            <span class="text-[11px] text-hris-text-secondary font-mono">
                                {{ $item->shift->time_range }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($item->attendance)
                            @if ($item->attendance->status === 'hadir')
                                <span class="pill-success">Hadir</span>
                            @elseif ($item->attendance->status === 'telat')
                                <span class="pill-warning">Telat</span>
                            @else
                                <span class="pill-danger">Alpha</span>
                            @endif
                        @elseif ($isPast)
                            <span class="pill-danger">Alpha</span>
                        @else
                            <span class="pill-muted">Mendatang</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
