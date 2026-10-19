@extends('layouts.admin')

@section('title', 'Master Data Pengguna')
@section('page-title', 'Data Pengguna & Karyawan')
@section('page-subtitle', 'Kelola akun Admin, Operator, dan Karyawan')

@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn-primary btn-md flex items-center gap-1.5">
        <x-icon name="plus" class="w-4 h-4" />
        <span>Tambah Pengguna</span>
    </a>
@endsection

@section('content')
<div class="flex flex-col gap-6 animate-fade-up">

    {{-- Filter Peran & Pencarian --}}
    <section class="glass-card p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <a href="{{ route('admin.users.index') }}"
               @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-surface-active text-hris-text' => empty($role), 'text-hris-text-secondary hover:bg-hris-surface' => !empty($role)])>
                <x-icon name="users" class="w-3.5 h-3.5" />
                <span>Semua ({{ $roleCounts['total'] }})</span>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'karyawan']) }}"
               @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-success-soft text-hris-success' => $role === 'karyawan', 'text-hris-text-secondary hover:bg-hris-surface' => $role !== 'karyawan'])>
                <x-icon name="user" class="w-3.5 h-3.5" />
                <span>Karyawan ({{ $roleCounts['karyawan'] }})</span>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'operator']) }}"
               @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-info-soft text-hris-info' => $role === 'operator', 'text-hris-text-secondary hover:bg-hris-surface' => $role !== 'operator'])>
                <x-icon name="shield" class="w-3.5 h-3.5" />
                <span>Operator ({{ $roleCounts['operator'] }})</span>
            </a>
            <a href="{{ route('admin.users.index', ['role' => 'admin']) }}"
               @class(['px-3 py-1.5 rounded-md font-semibold transition-colors flex items-center gap-1.5', 'bg-hris-primary-soft text-hris-primary' => $role === 'admin', 'text-hris-text-secondary hover:bg-hris-surface' => $role !== 'admin'])>
                <x-icon name="sparkles" class="w-3.5 h-3.5" />
                <span>Admin ({{ $roleCounts['admin'] }})</span>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
            @if ($role)
                <input type="hidden" name="role" value="{{ $role }}">
            @endif
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari nama, NIP, email..."
                   class="form-input text-xs py-1.5 px-3 w-64">
            <button type="submit" class="btn-surface btn-sm flex items-center gap-1.5">
                <x-icon name="search" class="w-3.5 h-3.5" />
                <span>Cari</span>
            </button>
        </form>
    </section>

    {{-- Tabel Pengguna --}}
    <section class="glass-card p-5 flex flex-col gap-4">
        <div class="overflow-x-auto">
            <table class="table-hris">
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>NIP</th>
                        <th>EMAIL</th>
                        <th>PERAN / ROLE</th>
                        <th>TERDAFTAR</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="font-bold text-hris-text">{{ $user->name }}</span>
                            </td>
                            <td class="font-mono text-hris-text-secondary">{{ $user->nip ?? '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->role === 'admin')
                                    <span class="pill-primary font-bold">Admin</span>
                                @elseif ($user->role === 'operator')
                                    <span class="pill-info font-bold">Operator</span>
                                @else
                                    <span class="pill-success font-bold">Karyawan</span>
                                @endif
                            </td>
                            <td class="text-hris-text-muted text-[11px]">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-surface btn-sm">
                                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                                        <span>Edit</span>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              data-confirm="Hapus akun {{ $user->name }}?"
                                              data-confirm-title="Hapus Akun Pengguna"
                                              data-confirm-message="Apakah Anda yakin ingin menghapus pengguna '{{ $user->name }}' ({{ $user->email }})? Seluruh data terkait pengguna ini akan dihapus dari sistem."
                                              data-confirm-btn="Ya, Hapus Pengguna"
                                              data-confirm-variant="danger"
                                              data-confirm-icon="trash">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">
                                                <x-icon name="trash" class="w-3.5 h-3.5" />
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-hris-text-muted">
                                Tidak ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $users->links() }}
        </div>
    </section>

</div>
@endsection
