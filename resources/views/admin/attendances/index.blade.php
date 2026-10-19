@extends('layouts.admin')

@section('title', 'Rekap Presensi Karyawan')
@section('page-title', 'Rekap Presensi')
@section('page-subtitle', 'Monitoring rekam kehadiran dan titik GPS seluruh karyawan')

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- Ringkasan Kehadiran Hari Terpilih --}}
    <section class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="glass-card p-4 flex flex-col gap-1">
            <span class="kpi-label">Total Absensi Tercatat</span>
            <span class="text-xl font-bold text-hris-text">{{ $daySummary['total'] }}</span>
        </div>
        <div class="glass-card p-4 flex flex-col gap-1">
            <span class="kpi-label">Tepat Waktu</span>
            <span class="text-xl font-bold text-hris-success">{{ $daySummary['hadir'] }}</span>
        </div>
        <div class="glass-card p-4 flex flex-col gap-1">
            <span class="kpi-label">Terlambat</span>
            <span class="text-xl font-bold text-hris-warning">{{ $daySummary['telat'] }}</span>
        </div>
        <div class="glass-card p-4 flex flex-col gap-1">
            <span class="kpi-label">Alpha / Tidak Hadir</span>
            <span class="text-xl font-bold text-hris-danger">{{ $daySummary['alpha'] }}</span>
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
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama karyawan atau NIP..."
                       class="form-input text-xs py-1.5 px-3">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary btn-sm">
                    Terapkan Filter
                </button>
                <a href="{{ route('admin.attendances.index') }}" class="btn-surface btn-sm">
                    Reset
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
                                <div class="flex items-center gap-2">
                                    <div class="avatar w-7 h-7 text-[10px]">
                                        {{ $att->user->initials }}
                                    </div>
                                    <span class="font-semibold text-hris-text">{{ $att->user->name }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-hris-text-secondary">{{ $att->user->nip ?? '-' }}</td>
                            <td>
                                @if ($att->schedule)
                                    <span class="font-medium text-hris-primary">{{ $att->schedule->shift->name }}</span>
                                @else
                                    <span class="text-hris-text-muted">Tanpa Shift</span>
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
                                <a href="{{ route('admin.attendances.show', $att) }}" class="btn-surface btn-sm">
                                    Detail
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
