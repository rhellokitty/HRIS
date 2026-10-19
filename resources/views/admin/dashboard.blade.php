@extends('layouts.admin')

@section('title', 'Dashboard Monitoring')
@section('page-title', 'Dashboard Monitoring')

@section('page-actions')
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.schedules.generate') }}"
              data-confirm="Generate jadwal otomatis untuk pekan depan?"
              data-confirm-title="Generate Jadwal Mingguan"
              data-confirm-message="Sistem akan menyusun plotting shift otomatis untuk seluruh karyawan aktif pada pekan depan berdasarkan shift default."
              data-confirm-btn="Ya, Generate Jadwal"
              data-confirm-variant="primary"
              data-confirm-icon="sparkles">
            @csrf
            <button type="submit" class="btn-primary btn-md flex items-center gap-1.5">
                <x-icon name="sparkles" class="w-4 h-4" />
                <span>Generate Jadwal</span>
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- 1. KPI Executive Metric Cards (4 Kolom - Design PRD §5.2 / iOS Style) --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Karyawan --}}
        <div class="glass-card p-5 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Total Karyawan</span>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                    <x-icon name="users" class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $totalEmployees }}</span>
                <span class="text-xs text-hris-text-secondary">Pegawai terdaftar aktif</span>
            </div>
        </div>

        {{-- Kehadiran Hari Ini --}}
        <div class="glass-card p-5 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Kehadiran Hari Ini</span>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                    <x-icon name="clipboard-check" class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $presentRate }}%</span>
                <span class="text-xs text-hris-text-secondary">
                    {{ $presentTodayCount }} dari {{ $totalEmployees }} hadir
                    @if ($lateTodayCount > 0)
                        · {{ $lateTodayCount }} terlambat
                    @endif
                </span>
            </div>
        </div>

        {{-- Cuti / Izin Hari Ini --}}
        <div class="glass-card p-5 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Sedang Cuti / Izin</span>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $onLeaveTodayCount }}</span>
                <span class="text-xs text-hris-text-secondary">Pegawai berhalangan / cuti</span>
            </div>
        </div>

        {{-- Pending Approvals --}}
        <div class="glass-card p-5 flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <span class="kpi-label">Pending Approval</span>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                    <x-icon name="clipboard" class="w-4 h-4" />
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $pendingLeaveCount }}</span>
                @if ($pendingLeaveCount > 0 && auth()->user()->isAdmin())
                    <a href="{{ route('admin.leaves.index') }}" class="text-xs font-medium text-hris-primary hover:underline flex items-center gap-1">
                        <span>Menunggu respon persetujuan</span>
                        <x-icon name="chevron-right" class="w-3 h-3" />
                    </a>
                @else
                    <span class="text-xs text-hris-text-secondary">Tidak ada antrean approval</span>
                @endif
            </div>
        </div>
    </section>

    {{-- 2. Master Shift Hari Ini --}}
    <section class="glass-card p-5 flex flex-col gap-3">
        <h3 class="section-title">Master Shift Operasional</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse ($activeShifts as $shift)
                <div class="glass-surface p-3.5 flex items-center justify-between rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                            <x-icon name="clock" class="h-4 w-4" />
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
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.attendances.index') }}" class="btn-surface btn-sm flex items-center gap-1.5">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5" />
                    <span>Lihat Rekap Absensi</span>
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
                                <span class="font-semibold text-hris-text">{{ $sched->user->name }}</span>
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
                                    <a href="{{ route('admin.attendances.show', $att) }}" class="btn-surface btn-sm inline-flex items-center gap-1.5">
                                        <x-icon name="external" class="w-3.5 h-3.5" />
                                        <span>Detail</span>
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
