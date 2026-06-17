@props([
    'size' => 'md',
    'variant' => 'icon',
])

@php
    $sizes = [
        'sm' => 36,
        'md' => 48,
        'lg' => 72,
        'xl' => 140,
    ];

    $height = $sizes[$size] ?? $sizes['md'];

    $src = $variant === 'full'
        ? asset('images/ksrelief-logo.png')
        : asset('images/ksrelief-logo-icon.png');
@endphp

<img
    src="{{ $src }}"
    alt="{{ $adminName }}"
    width="{{ $height }}"
    height="{{ $height }}"
    style="height: {{ $height }}px; width: auto; max-width: 100%; object-fit: contain;"
    {{ $attributes->merge(['class' => 'app-logo app-logo--' . $size]) }}
>
