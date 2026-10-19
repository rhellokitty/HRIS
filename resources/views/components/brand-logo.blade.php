@props(['size' => 'md'])

@php
    $dimensions = match ($size) {
        'sm' => 'h-8 w-8 rounded-lg',
        'lg' => 'h-10 w-10 rounded-xl',
        default => 'h-9 w-9 rounded-xl',
    };
    $iconSize = match ($size) {
        'sm' => 'h-4 w-4',
        'lg' => 'h-5 w-5',
        default => 'h-5 w-5',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex {$dimensions} shrink-0 items-center justify-center bg-hris-primary text-white shadow-none"]) }} aria-hidden="true">
    <x-icon name="briefcase" class="{{ $iconSize }}" />
</div>
