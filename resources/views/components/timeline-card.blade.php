@props([
    'title',
    'subtitle' => null,
    'items' => [],
    'empty' => null,
])

<x-card :title="$title" :subtitle="$subtitle">
    <div class="dashboard-timeline-list">
        @forelse($items as $item)
        <a href="{{ $item['url'] ?? '#' }}" class="dashboard-timeline-list__item @if(empty($item['url'])) pe-none @endif">
            <span class="dashboard-timeline-list__icon"><i class="ti {{ $item['icon'] ?? 'ti-calendar' }}"></i></span>
            <span class="dashboard-timeline-list__content">
                <span class="dashboard-timeline-list__title">{{ $item['title'] }}</span>
                @if(!empty($item['meta']))
                <span class="dashboard-timeline-list__meta">{{ $item['meta'] }}</span>
                @endif
            </span>
            <span class="dashboard-timeline-list__date">{{ $item['date'] ?? $item['at']?->format('Y-m-d') }}</span>
        </a>
        @empty
        <div class="text-center text-muted py-4">{{ $empty ?? __('dashboard.upcoming.empty') }}</div>
        @endforelse
    </div>
</x-card>
