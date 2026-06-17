@props([
    'label',
    'value',
    'icon' => 'ti-chart-bar',
    'variant' => 'primary',
    'trend' => null,
    'trendDirection' => 'up',
])

<x-stats-card
    :label="$label"
    :value="$value"
    :icon="$icon"
    :variant="$variant"
    :trend="$trend"
    :trendDirection="$trendDirection"
    {{ $attributes }}
/>
