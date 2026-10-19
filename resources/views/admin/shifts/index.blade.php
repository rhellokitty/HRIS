@extends('layouts.admin')

@section('title', 'Master Data Shift')
@section('page-title', 'Master Shift')
@section('page-subtitle', 'Manajemen jam kerja operasional karyawan')

@section('page-actions')
    <a href="{{ route('admin.shifts.create') }}" class="btn-primary btn-md flex items-center gap-1.5">
        <x-icon name="plus" class="w-4 h-4" />
        <span>Tambah Shift</span>
    </a>
@endsection

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    <section class="glass-card p-5 flex flex-col gap-4">
        <div class="overflow-x-auto">
            <table class="table-hris">
                <thead>
                    <tr>
                        <th>NAMA SHIFT</th>
                        <th>JAM MULAI</th>
                        <th>JAM SELESAI</th>
                        <th>DURASI</th>
                        <th>TIPE</th>
                        <th>TOTAL JADWAL</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr>
                            <td>
                                <span class="font-bold text-hris-text">{{ $shift->name }}</span>
                            </td>
                            <td class="font-mono">{{ $shift->start_label }} WIB</td>
                            <td class="font-mono">{{ $shift->end_label }} WIB</td>
                            <td>{{ $shift->duration_label }}</td>
                            <td>
                                @if ($shift->isOvernight())
                                    <span class="pill-warning">Lintas Hari</span>
                                @else
                                    <span class="pill-primary">Reguler</span>
                                @endif
                            </td>
                            <td>
                                <span class="pill-muted">{{ $shift->schedules_count }} Jadwal</span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.shifts.edit', $shift) }}" class="btn-surface btn-sm">
                                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        <span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}"
                                          data-confirm="Hapus shift {{ $shift->name }}?"
                                          data-confirm-title="Hapus Master Shift"
                                          data-confirm-message="Apakah Anda yakin ingin menghapus master shift '{{ $shift->name }}' ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})? Tindakan ini tidak dapat dibatalkan."
                                          data-confirm-btn="Ya, Hapus Shift"
                                          data-confirm-variant="danger"
                                          data-confirm-icon="trash">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-hris-text-muted">
                                Belum ada data shift kerja. Klik "Tambah Shift" untuk membuat baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
