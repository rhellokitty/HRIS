{{--
    LAYOUT ADMIN & OPERATOR — Desktop Dashboard (Design PRD §5.2)
    - Sidebar kiri 260px (sticky), konten kanan fleksibel max 1440px
    - Responsive: sidebar menjadi drawer di layar < lg
    - Borderless glass, zero box-shadow
--}}
@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?? 'Administrator';
    $isAdmin = $authUser?->role === 'admin';
    $initials = collect(preg_split('/\s+/', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
    $searchAction = \Illuminate\Support\Facades\Route::has('admin.users.index') && $isAdmin ? route('admin.users.index') : null;
    $logoutAction = \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : '#';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" class="dark">
<head>
    @include('partials.head')
</head>
<body class="admin-shell min-h-[100dvh] bg-hris-bg font-sans text-hris-text antialiased">

<div class="admin-dashboard-layout flex min-h-[100dvh]">

    {{-- Overlay (mobile drawer) --}}
    <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

    {{-- 1. Left Sidebar Navigation (260px) --}}
    <aside data-sidebar
           class="glass-sidebar no-scrollbar fixed inset-y-0 left-0 z-40 flex h-[100dvh] w-sidebar -translate-x-full flex-col justify-between gap-6 overflow-y-auto p-4 transition-transform duration-200 ease-hris lg:sticky lg:top-0 lg:translate-x-0"
           aria-label="Sidebar">
        <div class="flex flex-col gap-6">
            {{-- Brand Header --}}
            <div class="flex items-center justify-between gap-3 px-2 pt-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-hris-primary text-sm font-bold text-white">HR</div>
                    <div>
                        <h1 class="text-sm font-bold tracking-tight text-hris-text">{{ config('app.name', 'HRIS') }}</h1>
                        <p class="text-[11px] text-hris-text-muted">Shift &amp; Absensi</p>
                    </div>
                </div>
                <button type="button" data-sidebar-close class="btn-ghost btn-sm lg:hidden" aria-label="Tutup menu">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>

            {{-- Navigation Links --}}
            <nav class="flex flex-col gap-5" aria-label="Menu Admin">
                <div class="flex flex-col gap-1">
                    <p class="px-3 pb-1 text-micro uppercase text-hris-text-muted">Monitoring</p>
                    <x-admin.nav-link route="admin.dashboard" icon="grid">Dashboard</x-admin.nav-link>
                    <x-admin.nav-link route="admin.attendances.index" active="admin.attendances.*" icon="clipboard-check">Rekap Presensi</x-admin.nav-link>
                    <x-admin.nav-link route="admin.schedules.index" active="admin.schedules.*" icon="calendar">Jadwal Shift</x-admin.nav-link>
                </div>

                @if ($isAdmin)
                    <div class="flex flex-col gap-1">
                        <p class="px-3 pb-1 text-micro uppercase text-hris-text-muted">Master Data</p>
                        <x-admin.nav-link route="admin.shifts.index" active="admin.shifts.*" icon="briefcase">Master Shift</x-admin.nav-link>
                        <x-admin.nav-link route="admin.users.index" active="admin.users.*" icon="users">Data Pengguna</x-admin.nav-link>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="px-3 pb-1 text-micro uppercase text-hris-text-muted">Persetujuan</p>
                        <x-admin.nav-link route="admin.leaves.index" active="admin.leaves.*" icon="clipboard" :badge="($pendingLeaveCount ?? 0) ?: null">Approval Cuti</x-admin.nav-link>
                    </div>
                @endif
            </nav>
        </div>

        {{-- User Profile Card in Sidebar --}}
        <div class="flex flex-col gap-2">
            <button type="button" data-theme-toggle class="nav-link w-full">
                <x-icon name="moon" class="h-4 w-4" />
                <span>Tema</span>
                <span data-theme-label class="ml-auto text-[10px] text-hris-text-muted">Gelap</span>
            </button>

            <div class="glass-surface flex items-center justify-between gap-2 p-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <div class="avatar h-8 w-8 bg-hris-primary text-xs text-white">{{ $initials ?: 'AD' }}</div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-bold">{{ $displayName }}</p>
                        <p class="text-[10px] capitalize text-hris-text-muted">{{ $authUser?->role ?? 'admin' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ $logoutAction }}">
                    @csrf
                    <button type="submit" class="btn-ghost btn-sm" title="Keluar" aria-label="Keluar">
                        <x-icon name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- 2. Main Desktop View Area --}}
    <div class="flex min-w-0 flex-1 justify-center">
        <div class="flex w-full max-w-admin flex-col gap-6 p-4 lg:p-6">

            {{-- Top Bar --}}
            <header class="glass-panel flex flex-wrap items-center justify-between gap-3 p-4">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" data-sidebar-open class="btn-surface btn-sm lg:hidden" aria-label="Buka menu">
                        <x-icon name="menu" class="h-4 w-4" />
                    </button>
                    <div class="min-w-0">
                        <h2 class="page-title truncate">@yield('page-title', 'Dashboard')</h2>
                        @hasSection('page-subtitle')
                            <p class="caption truncate">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($searchAction)
                        <form method="GET" action="{{ $searchAction }}" class="hidden w-72 items-center gap-2 rounded-md bg-hris-surface px-3 py-2 text-xs text-hris-text-secondary md:flex" role="search">
                            <x-icon name="search" class="h-4 w-4 text-hris-text-muted" />
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIP"
                                   class="w-full border-none bg-transparent text-xs text-hris-text outline-none placeholder:text-hris-text-muted focus:ring-0">
                        </form>
                    @endif

                    <span class="hidden rounded-md bg-hris-surface px-3 py-2 text-xs font-medium text-hris-text-secondary xl:inline-flex">
                        {{ now()->translatedFormat('l, d M Y') }}
                    </span>

                    @yield('page-actions')
                </div>
            </header>

            @include('partials.flash')

            <main class="flex flex-col gap-6">
                @yield('content')
            </main>

            <footer class="pb-2 text-center text-[11px] text-hris-text-muted">
                &copy; {{ now()->year }} {{ config('app.name', 'HRIS') }} · Waktu server {{ now()->format('H:i') }} {{ now()->format('T') }}
            </footer>
        </div>
    </div>
</div>

@include('partials.confirm-modal')

@stack('scripts')
</body>
</html>
