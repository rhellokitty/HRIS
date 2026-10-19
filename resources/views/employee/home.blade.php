@extends('layouts.employee')

@section('title', 'Beranda Karyawan')

@section('content')
<div class="flex flex-col gap-5 animate-fade-up">

    {{-- Widget Utama: Presensi Cepat & Jam Server (Design PRD §5.1) --}}
    <section class="glass-card p-5 flex flex-col items-center gap-3 text-center">
        <span class="text-xs font-medium text-hris-text-muted tracking-wide" id="live-date">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>

        <div class="flex items-baseline justify-center gap-1 font-mono">
            <span class="text-display font-bold text-hris-text" id="live-clock">{{ now()->format('H:i:s') }}</span>
            <span class="text-xs font-sans text-hris-text-secondary">WIB</span>
        </div>

        @if ($schedule)
            <div class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-md bg-hris-surface text-xs text-hris-text-secondary">
                <x-icon name="briefcase" class="w-4 h-4 text-hris-primary" />
                <span>Shift {{ $schedule->shift->name }} ({{ $schedule->shift->time_range }})</span>
            </div>
        @else
            <div class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-md bg-hris-surface text-xs text-hris-text-secondary">
                <x-icon name="calendar" class="w-4 h-4 text-hris-text-muted" />
                <span>Tidak ada jadwal shift terjadwal hari ini</span>
            </div>
        @endif

        {{-- Status aksi presensi --}}
        @if ($action === 'clock_out')
            <div class="w-full p-3 rounded-md bg-hris-success-soft text-hris-success text-xs flex items-center justify-between">
                <span>Clock-In: <strong>{{ $openAttendance->clock_in_time->format('H:i') }} WIB</strong></span>
                <span class="pill-success">{{ $openAttendance->status_label }}</span>
            </div>
            <a href="{{ route('employee.attendance.index') }}" class="btn-danger btn-lg w-full text-center">
                Clock-Out Sekarang
            </a>
        @elseif ($action === 'done')
            <div class="w-full p-3 rounded-md bg-hris-surface text-xs flex items-center justify-between">
                <span>Presensi Hari Ini Lengkap</span>
                <span class="pill-success">Selesai</span>
            </div>
            <a href="{{ route('employee.attendance.index') }}" class="btn-surface btn-lg w-full text-center">
                Lihat Detail Presensi
            </a>
        @elseif ($action === 'leave')
            <div class="w-full p-3 rounded-md bg-hris-info-soft text-hris-info text-xs text-center font-medium">
                {{ $statusMessage }}
            </div>
        @else
            <a href="{{ route('employee.attendance.index') }}" class="btn-primary btn-lg w-full text-center">
                Buka Menu Presensi
            </a>
        @endif
    </section>

    {{-- Quick KPI Counters Karyawan (Sisa Cuti, Hadir Bulan Ini, Terlambat) --}}
    <section class="grid grid-cols-3 gap-3">
        <div class="glass-card p-3 flex flex-col items-center text-center gap-1">
            <span class="text-[11px] font-medium text-hris-text-muted">Sisa Cuti</span>
            <span class="text-base font-bold text-hris-text">{{ $remainingLeave }} Hari</span>
        </div>
        <div class="glass-card p-3 flex flex-col items-center text-center gap-1">
            <span class="text-[11px] font-medium text-hris-text-muted">Hadir Bulan Ini</span>
            <span class="text-base font-bold text-hris-text">{{ $presentThisMonth }} Hari</span>
        </div>
        <div class="glass-card p-3 flex flex-col items-center text-center gap-1">
            <span class="text-[11px] font-medium text-hris-text-muted">Terlambat</span>
            <span class="text-base font-bold text-hris-text">{{ $lateThisMonth }}x</span>
        </div>
    </section>

    {{-- Pintasan Cepat --}}
    <section class="glass-card p-4 flex flex-col gap-3">
        <h3 class="section-title">Menu Cepat</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('employee.attendance.index') }}" class="glass-surface p-3 flex flex-col gap-1.5 rounded-xl transition-colors hover:bg-hris-surface-hover">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-hris-surface text-hris-primary">
                    <x-icon name="clock" class="w-4 h-4" />
                </div>
                <span class="font-bold text-xs text-hris-text">Presensi GPS</span>
                <span class="text-[10px] text-hris-text-muted">Catat kehadiran</span>
            </a>
            <a href="{{ route('employee.leave.create') }}" class="glass-surface p-3 flex flex-col gap-1.5 rounded-xl transition-colors hover:bg-hris-surface-hover">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-hris-surface text-hris-primary">
                    <x-icon name="plus" class="w-4 h-4" />
                </div>
                <span class="font-bold text-xs text-hris-text">Ajukan Cuti/Izin</span>
                <span class="text-[10px] text-hris-text-muted">Formulir baru</span>
            </a>
            <a href="{{ route('employee.schedule.index') }}" class="glass-surface p-3 flex flex-col gap-1.5 rounded-xl transition-colors hover:bg-hris-surface-hover">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-hris-surface text-hris-primary">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
                <span class="font-bold text-xs text-hris-text">Jadwal Shift</span>
                <span class="text-[10px] text-hris-text-muted">Lihat jadwal kerja</span>
            </a>
            <a href="{{ route('employee.attendance.history') }}" class="glass-surface p-3 flex flex-col gap-1.5 rounded-xl transition-colors hover:bg-hris-surface-hover">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-hris-surface text-hris-primary">
                    <x-icon name="clipboard-check" class="w-4 h-4" />
                </div>
                <span class="font-bold text-xs text-hris-text">Riwayat Absen</span>
                <span class="text-[10px] text-hris-text-muted">Data presensi saya</span>
            </a>
        </div>
    </section>

</div>

@push('scripts')
<script>
    // Live Clock Client-Side Display (Server-Side Time Locking tetap berlaku saat submit)
    function updateLiveClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('live-clock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateLiveClock, 1000);
</script>
@endpush
@endsection
