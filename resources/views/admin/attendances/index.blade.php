@extends('layouts.admin')

@section('title', 'Rekap Presensi Karyawan')
@section('page-title', 'Rekap Presensi')

@section('content')
    <div class="flex flex-col gap-6 animate-fade-up">

        {{-- 1. Ringkasan Kehadiran Hari Terpilih (iOS Style Cards) --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Absensi --}}
            <div class="glass-card p-5 flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Total Presensi</span>
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                        <x-icon name="clipboard" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $daySummary['total'] }}</span>
                    <span class="text-xs text-hris-text-secondary">Data presensi tercatat</span>
                </div>
            </div>

            {{-- Tepat Waktu --}}
            <div class="glass-card p-5 flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Tepat Waktu</span>
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                        <x-icon name="clipboard-check" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $daySummary['hadir'] }}</span>
                    <span class="text-xs text-hris-text-secondary">Sesuai jam shift kerja</span>
                </div>
            </div>

            {{-- Terlambat --}}
            <div class="glass-card p-5 flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Terlambat Masuk</span>
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                        <x-icon name="clock" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $daySummary['telat'] }}</span>
                    <span class="text-xs text-hris-text-secondary">Melebihi batas toleransi</span>
                </div>
            </div>

            {{-- Alpha / Tidak Hadir --}}
            <div class="glass-card p-5 flex flex-col justify-between gap-3">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Alpha / Tidak Hadir</span>
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hris-surface text-hris-primary">
                        <x-icon name="x-circle" class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-3xl font-bold tracking-tight text-hris-text">{{ $daySummary['alpha'] }}</span>
                    <span class="text-xs text-hris-text-secondary">Tidak ada clock-in</span>
                </div>
            </div>
        </section>

        {{-- Filter & Search Form --}}
        <section class="glass-card p-4">
            <form method="GET" action="{{ route('admin.attendances.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="filter-date" class="text-xs font-semibold text-hris-text-secondary">Tanggal:</label>
                    <input type="date" name="date" id="filter-date" value="{{ $selectedDate }}"
                        class="form-input text-xs py-1.5 px-3 w-auto">
                </div>

                <div class="flex items-center gap-2">
                    <label for="filter-status" class="text-xs font-semibold text-hris-text-secondary">Status:</label>
                    <select name="status" id="filter-status" class="form-input text-xs py-1.5 px-3 w-auto">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ $selectedStatus === 'hadir' ? 'selected' : '' }}>Tepat Waktu</option>
                        <option value="telat" {{ $selectedStatus === 'telat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="alpha" {{ $selectedStatus === 'alpha' ? 'selected' : '' }}>Alpha</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama karyawan atau NIP..."
                        class="form-input text-xs py-1.5 px-3">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary btn-sm flex items-center gap-1.5">
                        <x-icon name="search" class="w-3.5 h-3.5" />
                        <span>Terapkan Filter</span>
                    </button>
                    <a href="{{ route('admin.attendances.index') }}" class="btn-surface btn-sm flex items-center gap-1.5">
                        <x-icon name="refresh" class="w-3.5 h-3.5" />
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </section>

        {{-- Tabel Rekap Presensi --}}
        <section class="glass-card p-5 flex flex-col gap-4">
            <div class="overflow-x-auto">
                <table class="table-hris">
                    <thead>
                        <tr>
                            <th>KARYAWAN</th>
                            <th>NIP</th>
                            <th>SHIFT</th>
                            <th>JAM MASUK</th>
                            <th>JAM PULANG</th>
                            <th>DURASI</th>
                            <th>TITIK GPS</th>
                            <th>STATUS</th>
                            <th class="text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $att)
                            <tr>
                                <td>
                                    <span class="font-semibold text-hris-text">{{ $att->user->name }}</span>
                                </td>
                                <td class="font-mono text-hris-text-secondary">{{ $att->user->nip ?? '-' }}</td>
                                <td>
                                    @if ($att->schedule)
                                        <span class="pill-primary" title="{{ $att->schedule->shift->time_range }}">{{ $att->schedule->shift->name }}</span>
                                    @else
                                        <span class="pill-muted">Tanpa Shift</span>
                                    @endif
                                </td>
                                <td class="font-mono">
                                    {{ $att->clock_in_time?->format('H:i:s') ?? '—' }}
                                </td>
                                <td class="font-mono">
                                    {{ $att->clock_out_time?->format('H:i:s') ?? '—' }}
                                </td>
                                <td>
                                    {{ $att->work_duration_label ?? '—' }}
                                </td>
                                <td>
                                    @if ($att->lat_in && $att->lng_in)
                                        <a href="{{ $att->map_url_in }}" target="_blank" rel="noopener"
                                            class="text-[11px] text-hris-info hover:underline inline-flex items-center gap-1 font-mono">
                                            <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                            <span>GPS In</span>
                                        </a>
                                    @else
                                        <span class="text-hris-text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($att->status === 'hadir')
                                        <span class="pill-success">Tepat Waktu</span>
                                    @elseif ($att->status === 'telat')
                                        <span class="pill-warning">Terlambat</span>
                                    @else
                                        <span class="pill-danger">Alpha</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.attendances.show', $att) }}"
                                        class="btn-surface btn-sm inline-flex items-center gap-1.5">
                                        <x-icon name="external" class="w-3.5 h-3.5" />
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-8 text-hris-text-muted">
                                    Tidak ada data presensi yang sesuai kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $attendances->links() }}
            </div>
        </section>

    </div>
@endsection
