{{-- LAYOUT GUEST — halaman autentikasi (mobile-first, terpusat) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" class="dark">
<head>
    @include('partials.head')
</head>
<body class="employee-shell min-h-[100dvh] bg-hris-bg font-sans text-hris-text antialiased">
    {{-- Theme Toggle Melayang di Pojok Kanan Atas --}}
    <div class="fixed top-4 right-4 z-50">
        <button type="button" data-theme-toggle class="btn-surface btn-sm flex items-center gap-1.5" aria-label="Ganti tema">
            <x-icon name="sun" class="h-4 w-4" />
            <span data-theme-label>Gelap</span>
        </button>
    </div>

    <main class="mx-auto flex min-h-[100dvh] w-full max-w-md flex-col justify-center gap-5 px-4 py-10">

        @include('partials.flash', ['hideErrorSummary' => true])

        @yield('content')

        <p class="text-center text-[11px] text-hris-text-muted">&copy; {{ now()->year }} {{ config('app.name', 'HRIS') }}</p>
    </main>

    @stack('scripts')
</body>
</html>
