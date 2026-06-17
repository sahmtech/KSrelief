@props([
    'label',
    'value',
    'icon' => 'ti-chart-bar',
    'variant' => 'primary',
    'trend' => null,
    'trendDirection' => 'up',
])

<div {{ $attributes->merge(['class' => 'stats-card']) }}>
    <div class="stats-card__header">
        <div class="stats-card__icon stats-card__icon--{{ $variant }}">
            <i class="{{ $icon }}"></i>
        </div>
        @if($trend)
            <span class="stats-card__trend stats-card__trend--{{ $trendDirection }}">
                <i class="ti ti-trending-{{ $trendDirection === 'up' ? 'up' : 'down' }}"></i>
                {{ $trend }}
            </span>
        @endif
    </div>
    <div class="stats-card__value">{{ $value }}</div>
    <div class="stats-card__label">{{ $label }}</div>
</div>
