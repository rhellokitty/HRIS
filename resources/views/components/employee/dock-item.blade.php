@props([
    'route',
    'icon' => 'home',
    'active' => null,
])

@php
    $href = \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#';
    $isActive = request()->routeIs($active ?? $route);
@endphp

<a href="{{ $href }}"
   @class(['dock-item', 'dock-item-active' => $isActive])
   @if ($isActive) aria-current="page" @endif>
    <x-icon :name="$icon" class="h-5 w-5" />
    <span>{{ $slot }}</span>
</a>
