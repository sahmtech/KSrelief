@props([
    'title' => null,
    'subtitle' => null,
    'flush' => false,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'admin-card']) }}>
    @if($title || isset($header))
        <div class="admin-card__header">
            <div>
                @if($title)
                    <h3 class="admin-card__header-title">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="admin-card__header-subtitle">{{ $subtitle }}</p>
                @endif
                {{ $header ?? '' }}
            </div>
            @if(isset($actions))
                <div class="admin-card__header-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div @class([
        'admin-card__body',
        'admin-card__body--flush' => $flush,
        'admin-card__body--compact' => $compact,
    ])>
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="admin-card__footer">
            {{ $footer }}
        </div>
    @endif
</div>
