<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0A0F1D">
<meta name="color-scheme" content="dark light">

<title>@hasSection('title')@yield('title') · @endif{{ config('app.name', 'HRIS') }}</title>

{{-- Anti-FOUC: terapkan tema tersimpan sebelum CSS dirender (default: dark) --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('hris-theme');
            var theme = t === 'light' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            if (theme === 'dark') document.documentElement.classList.add('dark');
        } catch (e) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>

{{-- Typography: Poppins (Design PRD §4.1) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('head')
