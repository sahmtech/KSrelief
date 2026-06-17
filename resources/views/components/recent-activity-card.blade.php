@props([
    'title',
    'subtitle' => null,
    'items' => [],
    'empty' => null,
])

<x-card :title="$title" :subtitle="$subtitle">
    <div class="dashboard-recent-list">
        @forelse($items as $item)
        <a href="{{ $item['url'] ?? '#' }}" class="list-item text-decoration-none @if(empty($item['url'])) pe-none @endif">
            <div class="list-item__avatar"><i class="ti {{ $item['icon'] ?? 'ti-activity' }}"></i></div>
            <div class="list-item__content">
                <div class="title">{{ $item['title'] }}</div>
                <div class="meta">{{ $item['meta'] ?? '' }} · {{ $item['at']?->diffForHumans() }}</div>
            </div>
        </a>
        @empty
        <div class="text-center text-muted py-4">{{ $empty ?? __('dashboard.recent_activity.empty') }}</div>
        @endforelse
    </div>
</x-card>
