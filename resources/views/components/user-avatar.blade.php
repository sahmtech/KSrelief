@props([
    'user' => null,
    'name' => null,
    'src' => null,
    'initials' => null,
    'size' => 'md',
])

@php
    $displayName = $name ?? $user?->name ?? '';
    $imageUrl = $src ?? $user?->avatarUrl();
    $displayInitials = $initials ?? $user?->initials() ?? (mb_strtoupper(mb_substr(trim($displayName), 0, 2)) ?: '?');
    $sizeClass = match ($size) {
        'sm' => 'user-avatar--sm',
        'lg' => 'user-avatar--lg',
        'xl' => 'user-avatar--xl',
        default => 'user-avatar--md',
    };
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar '.$sizeClass.($imageUrl ? ' user-avatar--image' : '')]) }}>
    @if($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $displayName }}" class="user-avatar__img">
    @else
        <span class="user-avatar__initials">{{ $displayInitials }}</span>
    @endif
</div>
