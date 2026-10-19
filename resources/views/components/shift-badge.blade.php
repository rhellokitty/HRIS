@props([
    'shift' => null,
    'showTime' => false,
])

@php
    if (! $shift) {
        $name = null;
        $time = null;
    } elseif (is_string($shift)) {
        $name = $shift;
        $time = null;
    } else {
        $name = $shift->name ?? null;
        $time = $shift->time_range ?? null;
    }

    // Anti-duplikasi "Shift Shift Pagi":
    // Jika nama sudah berawalan "Shift ", pertahankan namanya.
    // Jika belum, tambahkan "Shift ".
    if ($name) {
        $cleanName = preg_match('/^shift\s+/i', trim($name))
            ? trim($name)
            : 'Shift ' . trim($name);
    } else {
        $cleanName = 'Tanpa Shift';
    }
@endphp

@if ($name)
    <span {{ $attributes->merge(['class' => 'pill-primary font-medium']) }} @if($time) title="{{ $time }} WIB" @endif>
        {{ $cleanName }}
        @if ($showTime && $time)
            <span class="opacity-80 font-mono text-[10px] ml-1">({{ $time }})</span>
        @endif
    </span>
@else
    <span {{ $attributes->merge(['class' => 'pill-muted']) }}>
        {{ $cleanName }}
    </span>
@endif
