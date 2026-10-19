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

        {{-- Top Bar: Sticky Clean Header Bergaya iOS (Mengikuti Scroll & Nama Saja) --}}
        <header class="sticky top-0 z-40 -mx-4 -mt-4 px-4 py-3.5 bg-hris-bg/80 backdrop-blur-xl border-b border-[var(--bg-surface)] flex items-center justify-between gap-3 transition-all">
            <div class="min-w-0">
                <h1 class="text-sm font-bold tracking-tight text-hris-text truncate">{{ $displayName }}</h1>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" data-theme-toggle class="btn-ghost btn-sm p-1.5 text-hris-text-muted hover:text-hris-text" aria-label="Ganti tema">
                    <x-icon name="sun" class="h-4 w-4" />
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm p-1.5 text-hris-text-muted hover:text-hris-danger" title="Keluar" aria-label="Keluar">
                        <x-icon name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>
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
