@props([
    'member',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => '32px',
        'md' => '40px',
        'lg' => '56px',
        'xl' => '80px',
    ];
    $fontSizes = [
        'sm' => '0.6875rem',
        'md' => '0.8125rem',
        'lg' => '1rem',
        'xl' => '1.25rem',
    ];
    $dim = $sizes[$size] ?? $sizes['md'];
    $font = $fontSizes[$size] ?? $fontSizes['md'];
@endphp

<div
    class="member-avatar d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-semibold flex-shrink-0"
    style="width: {{ $dim }}; height: {{ $dim }}; font-size: {{ $font }};"
    title="{{ $member->full_name }}"
>
    {{ $member->initials() }}
</div>
