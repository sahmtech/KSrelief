@props([
    'title',
    'subtitle' => null,
    'config' => [],
    'height' => 300,
])

<x-card :title="$title" :subtitle="$subtitle">
    <div class="dashboard-chart" data-chart='@json($config)' style="min-height: {{ $height }}px;"></div>
</x-card>
