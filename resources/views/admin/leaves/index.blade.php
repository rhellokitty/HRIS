@extends('layouts.admin')

@section('title', 'Persetujuan Cuti & Izin')
@section('page-title', 'Approval Cuti & Izin')
@section('page-subtitle', 'Persetujuan permohonan cuti, izin, dan surat sakit karyawan')

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- Filter Status Tabs --}}
    <section class="glass-card p-3 flex flex-wrap items-center gap-2 text-xs">
        <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}"
           @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-warning-soft text-hris-warning' => $status === 'pending', 'text-hris-text-secondary hover:bg-hris-surface' => $status !== 'pending'])>
            <span>Menunggu Respon</span>
            <span class="rounded-full bg-hris-warning/20 px-1.5 py-0.2 text-[10px] font-bold">{{ $counts['pending'] }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => 'approved']) }}"
           @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-success-soft text-hris-success' => $status === 'approved', 'text-hris-text-secondary hover:bg-hris-surface' => $status !== 'approved'])>
            <span>Disetujui</span>
            <span class="rounded-full bg-hris-success/20 px-1.5 py-0.2 text-[10px] font-bold">{{ $counts['approved'] }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => 'rejected']) }}"
           @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-danger-soft text-hris-danger' => $status === 'rejected', 'text-hris-text-secondary hover:bg-hris-surface' => $status !== 'rejected'])>
            <span>Ditolak</span>
            <span class="rounded-full bg-hris-danger/20 px-1.5 py-0.2 text-[10px] font-bold">{{ $counts['rejected'] }}</span>
        </a>

        <a href="{{ route('admin.leaves.index', ['status' => '']) }}"
           @class(['px-3 py-1.5 rounded-md font-semibold transition-colors', 'bg-hris-surface-active text-hris-text' => empty($status), 'text-hris-text-secondary hover:bg-hris-surface' => !empty($status)])>
            Semua
        </a>
    </section>

    {{-- Tabel Pengajuan Cuti --}}
    <section class="glass-card p-5 flex flex-col gap-4">
        <div class="overflow-x-auto">
            <table class="table-hris">
                <thead>
                    <tr>
                        <th>KARYAWAN</th>
                        <th>NIP</th>
                        <th>JENIS</th>
                        <th>PERIODE</th>
                        <th>DURASI</th>
                        <th>ALASAN</th>
                        <th>STATUS</th>
                        <th class="text-right">AKSI APPROVAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves as $leave)
                        <tr>
                            <td>
                                <span class="font-semibold text-hris-text">{{ $leave->user->name }}</span>
                            </td>
                            <td class="font-mono text-hris-text-secondary">{{ $leave->user->nip ?? '-' }}</td>
                            <td>
                                <span class="pill-primary font-bold">{{ $leave->type_label }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $leave->start_date->translatedFormat('d M Y') }} – {{ $leave->end_date->translatedFormat('d M Y') }}
                            </td>
                            <td>{{ $leave->duration_days }} hari</td>
                            <td class="max-w-xs truncate" title="{{ $leave->reason }}">
                                {{ $leave->reason }}
                            </td>
                            <td>
                                @if ($leave->status === 'approved')
                                    <span class="pill-success">Disetujui</span>
                                @elseif ($leave->status === 'rejected')
                                    <span class="pill-danger">Ditolak</span>
                                @else
                                    <span class="pill-warning">Menunggu</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($leave->isPending())
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Aksi Terima (PRD §3.C) --}}
                                        <form method="POST" action="{{ route('admin.leaves.approve', $leave) }}">
                                            @csrf
                                            <button type="submit" class="btn-success btn-sm flex items-center gap-1">
                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                                <span>Terima</span>
                                            </button>
                                        </form>

                                        {{-- Aksi Tolak (PRD §3.C) --}}
                                        <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}"
                                              data-confirm="Tolak permohonan cuti {{ $leave->user->name }}?"
                                              data-confirm-title="Tolak Permohonan Cuti"
                                              data-confirm-message="Apakah Anda yakin ingin menolak permohonan cuti untuk {{ $leave->user->name }} ({{ $leave->start_date->translatedFormat('d M Y') }} s/d {{ $leave->end_date->translatedFormat('d M Y') }})?"
                                              data-confirm-btn="Ya, Tolak Permohonan"
                                              data-confirm-variant="danger"
                                              data-confirm-icon="exclamation">
                                            @csrf
                                            <button type="submit" class="btn-danger btn-sm flex items-center gap-1">
                                                <x-icon name="x" class="w-3.5 h-3.5" />
                                                <span>Tolak</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[10px] text-hris-text-muted">Sudah Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-hris-text-muted">
                                Tidak ada pengajuan cuti/izin pada kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $leaves->links() }}
        </div>
    </section>

</div>
@endsection
