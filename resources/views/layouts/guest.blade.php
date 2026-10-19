{{-- LAYOUT GUEST — halaman autentikasi (mobile-first, terpusat) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" class="dark">
<head>
    @include('partials.head')
</head>
<body class="employee-shell min-h-[100dvh] bg-hris-bg font-sans text-hris-text antialiased">
    <main class="mx-auto flex min-h-[100dvh] w-full max-w-md flex-col justify-center gap-5 px-4 py-10">
        <div class="flex items-center justify-between px-1">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-hris-primary text-sm font-bold text-white">HR</div>
                <div>
                    <h1 class="text-sm font-bold tracking-tight">{{ config('app.name', 'HRIS') }}</h1>
                    <p class="text-[11px] text-hris-text-muted">Shift &amp; Absensi</p>
                </div>
            </div>
            <button type="button" data-theme-toggle class="btn-surface btn-sm" aria-label="Ganti tema">
                <x-icon name="sun" class="h-4 w-4" />
                <span data-theme-label>Gelap</span>
            </button>
        </div>

        @include('partials.flash', ['hideErrorSummary' => true])

        @yield('content')

        <p class="text-center text-[11px] text-hris-text-muted">&copy; {{ now()->year }} {{ config('app.name', 'HRIS') }}</p>
    </main>

    @stack('scripts')
</body>
</html>
