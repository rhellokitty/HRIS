{{-- Layout halaman error (tidak bergantung pada session / $errors) --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" class="dark">
<head>
    @include('partials.head')
</head>
<body class="employee-shell min-h-[100dvh] bg-hris-bg font-sans text-hris-text antialiased">
    <main class="mx-auto flex min-h-[100dvh] w-full max-w-md flex-col items-center justify-center gap-5 px-4 py-10 text-center">
        <section class="glass-panel flex w-full flex-col items-center gap-4 p-8">
            <span class="pill-primary">@yield('code')</span>
            <h1 class="page-title">@yield('message')</h1>
            <p class="caption">@yield('description')</p>
            <a href="{{ url('/') }}" class="btn-primary mt-2 w-full">Kembali</a>
        </section>
    </main>
</body>
</html>
