@extends('layouts.employee')

@section('title', 'Daftar Cuti & Izin')

@section('content')
<div class="flex flex-col gap-4 animate-fade-up">

    <div class="flex items-center justify-between px-1">
        <h2 class="page-title">Cuti &amp; Izin</h2>
        <a href="{{ route('employee.leave.create') }}" class="btn-primary btn-sm inline-flex items-center gap-1">
            <x-icon name="plus" class="w-3.5 h-3.5" />
            <span>Ajukan</span>
        </a>
    </div>

    @if ($leaves->isEmpty())
        <div class="glass-card p-8 text-center flex flex-col items-center gap-2">
            <x-icon name="clipboard" class="w-10 h-10 text-hris-text-muted" />
            <p class="font-semibold text-xs text-hris-text">Belum ada pengajuan cuti/izin</p>
            <p class="caption">Ajukan cuti tahunan, izin keperluan, atau sakit melalui tombol di atas.</p>
            <a href="{{ route('employee.leave.create') }}" class="btn-primary btn-sm mt-2">
                Buat Pengajuan Baru
            </a>
        </div>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($leaves as $leave)
                <div class="glass-card p-4 flex flex-col gap-2.5">
                    <div class="flex items-center justify-between">
                        <span class="pill-primary font-bold">{{ $leave->type_label }}</span>

                        @if ($leave->status === 'approved')
                            <span class="pill-success">Disetujui</span>
                        @elseif ($leave->status === 'rejected')
                            <span class="pill-danger">Ditolak</span>
                        @else
                            <span class="pill-warning">Menunggu</span>
                        @endif
                    </div>

                    <div class="text-xs">
                        <p class="font-bold text-hris-text">
                            {{ $leave->start_date->translatedFormat('d M Y') }} – {{ $leave->end_date->translatedFormat('d M Y') }}
                            <span class="text-hris-text-muted font-normal">({{ $leave->duration_days }} hari)</span>
                        </p>
                        <p class="text-hris-text-secondary mt-1 bg-hris-surface p-2.5 rounded-md">
                            {{ $leave->reason }}
                        </p>
                    </div>

                    <span class="text-[10px] text-hris-text-muted">
                        Diajukan pada {{ $leave->created_at->translatedFormat('d M Y, H:i') }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-2">
            {{ $leaves->links() }}
        </div>
    @endif

</div>
@endsection
