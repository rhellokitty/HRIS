@extends('layouts.admin')

@section('title', 'Dashboard Monitoring')
@section('page-title', 'Dashboard Monitoring')
@section('page-subtitle', 'Ringkasan kehadiran dan operasional shift real-time')

@section('page-actions')
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.schedules.generate') }}">
            @csrf
            <button type="submit" class="btn-primary btn-md flex items-center gap-1.5"
                    onclick="return confirm('Generate jadwal otomatis untuk pekan depan?')">
                <x-icon name="sparkles" class="w-4 h-4" />
                <span>Generate Jadwal</span>
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- 1. KPI Executive Metric Cards (4 Kolom - Design PRD §5.2) --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Karyawan --}}
        <div class="glass-card p-5 flex flex-col gap-2">
            <span class="kpi-label">Total Karyawan</span>
            <div class="flex items-baseline justify-between">
                <span class="kpi-value">{{ $totalEmployees }}</span>
                <span class="text-xs font-medium text-hris-primary">Pegawai</span>
            </div>
        </div>

        {{-- Kehadiran Hari Ini --}}
        <div class="glass-card p-5 flex flex-col gap-2">
            <span class="kpi-label">Kehadiran Hari Ini</span>
            <div class="flex items-baseline justify-between">
                <span class="kpi-value text-hris-success">{{ $presentRate }}%</span>
                <span class="text-xs font-medium text-hris-text-secondary">{{ $presentTodayCount }} hadir</span>
            </div>
            @if ($lateTodayCount > 0)
                <span class="text-[11px] text-hris-warning">{{ $lateTodayCount }} terlambat masuk</span>
            @endif
        </div>

        {{-- Cuti / Izin Hari Ini --}}
        <div class="glass-card p-5 flex flex-col gap-2">
            <span class="kpi-label">Sedang Cuti / Izin</span>
            <div class="flex items-baseline justify-between">
                <span class="kpi-value text-hris-info">{{ $onLeaveTodayCount }}</span>
                <span class="text-xs font-medium text-hris-text-secondary">Staf</span>
            </div>
        </div>

        {{-- Pending Approvals --}}
        <div class="glass-card p-5 flex flex-col gap-2">
            <span class="kpi-label">Pending Approval</span>
            <div class="flex items-baseline justify-between">
                <span class="kpi-value text-hris-warning">{{ $pendingLeaveCount }}</span>
                @if ($pendingLeaveCount > 0 && auth()->user()->isAdmin())
                    <a href="{{ route('admin.leaves.index') }}" class="text-xs font-semibold text-hris-warning hover:underline">
                        Perlu respon &rarr;
                    </a>
                @else
                    <span class="text-xs font-medium text-hris-text-secondary">Terkini</span>
                @endif
            </div>
        </div>
    </section>

    {{-- 2. Master Shift Hari Ini --}}
    <section class="glass-card p-5 flex flex-col gap-3">
        <h3 class="section-title">Master Shift Operasional</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse ($activeShifts as $shift)
                <div class="glass-surface p-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-md bg-hris-primary-soft text-hris-primary flex items-center justify-center font-bold text-xs">
                            {{ substr($shift->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="font-bold text-xs text-hris-text">{{ $shift->name }}</p>
                            <p class="caption font-mono">{{ $shift->time_range }}</p>
                        </div>
                    </div>
                    <span class="pill-primary text-[10px]">
                        {{ $shift->schedules_count }} Jadwal Hari Ini
                    </span>
                </div>
            @empty
                <p class="caption col-span-3">Belum ada master shift.</p>
            @endforelse
        </div>
    </section>

    {{-- 3. Live Shift Operasional & Monitoring Kehadiran (PRD §3.D) --}}
    <section class="glass-card p-5 flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="section-title">Jadwal Shift Operasional Hari Ini</h3>
                <p class="caption">Live status kehadiran per karyawan untuk tanggal {{ $today->translatedFormat('d F Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.attendances.index') }}" class="btn-surface btn-sm">
                    Lihat Rekap Absensi
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-hris">
                <thead>
                    <tr>
                        <th>KARYAWAN</th>
                        <th>NIP</th>
                        <th>SHIFT KERJA</th>
                        <th>JAM MASUK</th>
                        <th>JAM PULANG</th>
                        <th>STATUS</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($todaySchedules as $sched)
                        @php
                            $att = $sched->attendance;
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="avatar w-7 h-7 text-[10px]">
                                        {{ $sched->user->initials }}
                                    </div>
                                    <span class="font-semibold text-hris-text">{{ $sched->user->name }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-hris-text-secondary">{{ $sched->user->nip ?? '-' }}</td>
                            <td>
                                <span class="font-bold text-hris-primary">{{ $sched->shift->name }}</span>
                                <span class="text-[11px] text-hris-text-muted">({{ $sched->shift->time_range }})</span>
                            </td>
                            <td class="font-mono">
                                {{ $att?->clock_in_time?->format('H:i:s') ?? '—' }}
                            </td>
                            <td class="font-mono">
                                {{ $att?->clock_out_time?->format('H:i:s') ?? '—' }}
                            </td>
                            <td>
                                @if ($att)
                                    @if ($att->status === 'hadir')
                                        <span class="pill-success">Tepat Waktu</span>
                                    @elseif ($att->status === 'telat')
                                        <span class="pill-warning">Terlambat</span>
                                    @else
                                        <span class="pill-danger">Alpha</span>
                                    @endif
                                @elseif ($sched->hasEnded())
                                    <span class="pill-danger">Alpha (Tidak Hadir)</span>
                                @elseif ($sched->isRunning())
                                    <span class="pill-warning">Sedang Berjalan</span>
                                @else
                                    <span class="pill-muted">Belum Dimulai</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($att)
                                    <a href="{{ route('admin.attendances.show', $att) }}" class="text-hris-primary font-semibold hover:underline">
                                        Detail
                                    </a>
                                @else
                                    <span class="text-hris-text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-hris-text-muted">
                                Tidak ada jadwal shift operasional untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
