@props([
    'route',             // nama route tujuan
    'icon' => 'grid',
    'active' => null,    // pola routeIs, default = nama route
    'badge' => null,     // angka badge (mis. pending approval)
    'params' => [],
])

@php
    $href = \Illuminate\Support\Facades\Route::has($route) ? route($route, $params) : '#';
    $isActive = request()->routeIs($active ?? $route);
@endphp

<a href="{{ $href }}"
   @class(['nav-link', 'nav-link-active' => $isActive])
   @if ($isActive) aria-current="page" @endif>
    <x-icon :name="$icon" class="h-4 w-4" />
    <span class="truncate">{{ $slot }}</span>
    @if ($badge)
        <span class="ml-auto rounded-full bg-hris-warning-soft px-1.5 py-0.5 text-[10px] font-bold text-hris-warning">{{ $badge }}</span>
    @endif
</a>
