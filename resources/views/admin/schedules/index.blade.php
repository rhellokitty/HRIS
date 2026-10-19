@extends('layouts.admin')

@section('title', 'Jadwal Shift Karyawan')
@section('page-title', 'Jadwal Shift Karyawan')
@section('page-subtitle', 'Plotting dan pengelolaan jadwal kerja tim')

@section('page-actions')
    @if (auth()->user()->isAdmin())
        <div class="flex items-center gap-2">
            {{-- Tombol Generate Jadwal Mingguan (PRD §3.B) --}}
            <form method="POST" action="{{ route('admin.schedules.generate') }}">
                @csrf
                <input type="hidden" name="start_date" value="{{ $selectedDate->copy()->addWeek()->startOfWeek()->toDateString() }}">
                <button type="submit" class="btn-primary btn-md flex items-center gap-1.5"
                        onclick="return confirm('Generate jadwal otomatis untuk pekan depan (mulai {{ $selectedDate->copy()->addWeek()->startOfWeek()->translatedFormat('d M Y') }})?')">
                    <x-icon name="sparkles" class="w-4 h-4" />
                    <span>Generate Jadwal Mingguan</span>
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- Filter Bar & Navigation --}}
    <section class="glass-card p-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.schedules.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="date-picker" class="text-xs font-semibold text-hris-text-secondary">Pilih Tanggal:</label>
                <input type="date" name="date" id="date-picker" value="{{ $selectedDate->toDateString() }}"
                       onchange="this.form.submit()"
                       class="form-input text-xs py-1.5 px-3 w-auto">
            </div>

            <div class="flex items-center gap-1 bg-hris-surface p-1 rounded-md text-xs">
                <a href="{{ route('admin.schedules.index', ['date' => $selectedDate->toDateString(), 'view' => 'day']) }}"
                   @class(['px-3 py-1 rounded font-semibold transition-colors', 'bg-hris-primary text-white' => $viewMode === 'day', 'text-hris-text-secondary hover:text-hris-text' => $viewMode !== 'day'])>
                    Harian
                </a>
                <a href="{{ route('admin.schedules.index', ['date' => $selectedDate->toDateString(), 'view' => 'week']) }}"
                   @class(['px-3 py-1 rounded font-semibold transition-colors', 'bg-hris-primary text-white' => $viewMode === 'week', 'text-hris-text-secondary hover:text-hris-text' => $viewMode !== 'week'])>
                    Mingguan (Kalender)
                </a>
            </div>
        </form>

        <div class="text-xs text-hris-text-secondary">
            Periode: <strong>{{ $startDate->translatedFormat('d M Y') }}</strong>
            @if ($viewMode === 'week')
                s/d <strong>{{ $endDate->translatedFormat('d M Y') }}</strong>
            @endif
        </div>
    </section>

    @if (auth()->user()->isAdmin())
        {{-- Form Tambah Plotting Jadwal Manual --}}
        <section class="glass-card p-5 flex flex-col gap-3">
            <h3 class="section-title">Plotting Jadwal Manual</h3>
            <form method="POST" action="{{ route('admin.schedules.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label for="user_id" class="form-label">Karyawan</label>
                    <select name="user_id" id="user_id" required class="form-input text-xs">
                        <option value="">Pilih Karyawan...</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('user_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->nip ?? 'No NIP' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="shift_id" class="form-label">Shift</label>
                    <select name="shift_id" id="shift_id" required class="form-input text-xs">
                        <option value="">Pilih Shift...</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }} ({{ $shift->time_range }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="manual-date" class="form-label">Tanggal</label>
                    <input type="date" name="date" id="manual-date" required
                           value="{{ old('date', $selectedDate->toDateString()) }}"
                           class="form-input text-xs">
                </div>

                <div>
                    <button type="submit" class="btn-primary btn-md w-full">
                        Tambah Jadwal
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- Tampilan Kalender Mingguan --}}
    @if ($viewMode === 'week')
        <section class="glass-card p-5 flex flex-col gap-4">
            <h3 class="section-title">Peta Jadwal Sepekan</h3>

            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                @for ($d = 0; $d < 7; $d++)
                    @php
                        $dayDate = $startDate->copy()->addDays($d);
                        $dayKey = $dayDate->toDateString();
                        $daySchedules = $schedules->get($dayKey, collect());
                        $isToday = $dayDate->isToday();
                    @endphp
                    <div @class([
                        'glass-surface p-3 flex flex-col gap-2 rounded-lg min-h-[160px]',
                        'ring-1 ring-hris-primary/60 bg-hris-primary-soft/10' => $isToday,
                    ])>
                        <div class="flex items-center justify-between pb-1 border-b border-[var(--bg-surface)]">
                            <span class="font-bold text-xs {{ $isToday ? 'text-hris-primary' : 'text-hris-text' }}">
                                {{ $dayDate->translatedFormat('D, d M') }}
                            </span>
                            @if ($isToday)
                                <span class="pill-success text-[9px] px-1 py-0.2">Hari ini</span>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1.5 overflow-y-auto max-h-60 no-scrollbar">
                            @forelse ($daySchedules as $sched)
                                <div class="bg-[var(--bg-card-solid)] p-2 rounded text-[11px] flex flex-col gap-0.5">
                                    <span class="font-semibold text-hris-text truncate">{{ $sched->user->name }}</span>
                                    <span class="text-hris-primary font-medium text-[10px]">{{ $sched->shift->name }}</span>
                                    <span class="text-hris-text-muted text-[9px] font-mono">{{ $sched->shift->time_range }}</span>
                                </div>
                            @empty
                                <span class="text-[10px] text-hris-text-muted text-center py-4">Tidak ada jadwal</span>
                            @endforelse
                        </div>
                    </div>
                @endfor
            </div>
        </section>

    {{-- Tampilan Harian (Tabel Lengkap) --}}
    @else
        <section class="glass-card p-5 flex flex-col gap-4">
            <h3 class="section-title">Daftar Jadwal Tanggal {{ $selectedDate->translatedFormat('d F Y') }}</h3>

            <div class="overflow-x-auto">
                <table class="table-hris">
                    <thead>
                        <tr>
                            <th>KARYAWAN</th>
                            <th>NIP</th>
                            <th>SHIFT</th>
                            <th>JAM KERJA</th>
                            <th>PRESENSI HARI INI</th>
                            @if (auth()->user()->isAdmin())
                                <th class="text-right">AKSI</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $item)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar w-7 h-7 text-[10px]">
                                            {{ $item->user->initials }}
                                        </div>
                                        <span class="font-semibold text-hris-text">{{ $item->user->name }}</span>
                                    </div>
                                </td>
                                <td class="font-mono text-hris-text-secondary">{{ $item->user->nip ?? '-' }}</td>
                                <td>
                                    <span class="font-bold text-hris-primary">{{ $item->shift->name }}</span>
                                </td>
                                <td class="font-mono">{{ $item->shift->time_range }} WIB</td>
                                <td>
                                    @if ($item->attendance)
                                        @if ($item->attendance->status === 'hadir')
                                            <span class="pill-success">Hadir ({{ $item->attendance->clock_in_time?->format('H:i') }})</span>
                                        @elseif ($item->attendance->status === 'telat')
                                            <span class="pill-warning">Telat ({{ $item->attendance->clock_in_time?->format('H:i') }})</span>
                                        @else
                                            <span class="pill-danger">Alpha</span>
                                        @endif
                                    @else
                                        <span class="pill-muted">Belum Ada Presensi</span>
                                    @endif
                                </td>
                                @if (auth()->user()->isAdmin())
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.schedules.destroy', $item) }}"
                                              data-confirm="Hapus plotting jadwal ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-hris-text-muted">
                                    Tidak ada jadwal shift pada tanggal ini. Klik "Generate Jadwal Mingguan" untuk membuat otomatis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $schedules->links() }}
            </div>
        </section>
    @endif

</div>
@endsection
