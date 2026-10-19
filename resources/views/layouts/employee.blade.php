{{--
    LAYOUT KARYAWAN — Mobile-First Employee Self-Service (Design PRD §5.1)
    - Container dikunci max-w-md mx-auto (448px)
    - Natural document scrolling (tidak mengunci overflow/height)
    - Dock clearance: padding-bottom max(150px, calc(120px + safe-area))
    - Floating bottom dock (pill) 5 tab, tanpa sticky hover
--}}
@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?? 'Karyawan';
    $initials = collect(preg_split('/\s+/', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" class="dark">
<head>
    @include('partials.head')
</head>
<body class="employee-shell min-h-[100dvh] bg-hris-bg font-sans text-hris-text antialiased">

    <main class="relative mx-auto flex w-full max-w-md flex-col gap-5 px-4 pt-4 pb-dock">

        {{-- Top Bar: Avatar, Nama & Status Shift --}}
        <header class="glass-card flex items-center justify-between gap-3 p-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-hris-primary-soft text-hris-primary">
                    <x-icon name="user" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-sm font-bold tracking-tight">{{ $displayName }}</h2>
                    <p class="truncate text-xs text-hris-text-secondary">
                        @if ($authUser?->nip)
                            NIP {{ $authUser->nip }} ·
                        @endif
                        {{ ucfirst($authUser?->role ?? 'karyawan') }}
                    </p>
                </div>
            </div>

            @hasSection('header-badge')
                @yield('header-badge')
            @elseif (isset($employeeTodaySchedule) && $employeeTodaySchedule)
                <span class="pill-success">Shift {{ $employeeTodaySchedule->shift->name }}</span>
            @else
                <span class="pill-muted">Libur</span>
            @endif
        </header>

        @hasSection('page-header')
            <div class="flex items-end justify-between gap-3 px-1">
                @yield('page-header')
            </div>
        @endif

        @include('partials.flash')

        @yield('content')
    </main>

    {{-- Floating Bottom Navigation Dock --}}
    <nav class="fixed bottom-5 left-1/2 z-50 w-auto max-w-[92vw] -translate-x-1/2" aria-label="Navigasi Karyawan"
         style="margin-bottom: env(safe-area-inset-bottom);">
        <div class="glass-dock flex items-center gap-1 px-3 py-1.5">
            <x-employee.dock-item route="employee.home" icon="home">Beranda</x-employee.dock-item>
            <x-employee.dock-item route="employee.attendance.index" active="employee.attendance.*" icon="clock">Presensi</x-employee.dock-item>

            @php
                $fabHref = \Illuminate\Support\Facades\Route::has('employee.leave.create') ? route('employee.leave.create') : '#';
            @endphp
            <a href="{{ $fabHref }}" class="dock-fab mx-1" aria-label="Pengajuan Baru">
                <x-icon name="plus" class="h-5 w-5" stroke="2.5" />
            </a>

            <x-employee.dock-item route="employee.schedule.index" active="employee.schedule.*" icon="calendar">Jadwal</x-employee.dock-item>
            <x-employee.dock-item route="employee.profile" active="employee.profile*" icon="user">Akun</x-employee.dock-item>
        </div>
    </nav>

    @include('partials.confirm-modal')

    @stack('scripts')
</body>
</html>
