@props(['timeline'])

<div class="workflow-timeline">
    @foreach($timeline as $item)
    @php
        $stage = $item['stage'];
        $stageColor = $stage->color ?? '#6B7280';
        $statusClass = $item['completed'] ? 'completed' : ($item['current'] ? 'current' : 'pending');
    @endphp
    <div class="timeline-item timeline-item--{{ $statusClass }}">
        <div class="timeline-dot" style="--dot-color: {{ $stageColor }};">
            @if($item['completed'])
                <i class="ti ti-check"></i>
            @elseif($item['current'])
                <i class="ti ti-player-play"></i>
            @else
                <span></span>
            @endif
        </div>
        <div class="timeline-content">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-semibold" style="color: {{ $stageColor }};">{{ $stage->name }}</span>
                @if($item['current'])
                    <span class="badge bg-primary-subtle text-primary small">{{ __('workflow.timeline.current') }}</span>
                @elseif($item['completed'])
                    <span class="badge bg-success-subtle text-success small">{{ __('workflow.timeline.completed') }}</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary small">{{ __('workflow.timeline.pending') }}</span>
                @endif
            </div>
            @if($item['history'])
                <p class="text-muted small mb-0">
                    <i class="ti ti-user me-1"></i>{{ __('workflow.timeline.changed_by') }}: {{ $item['history']->changedBy?->name ?? '—' }}
                    &nbsp;·&nbsp;<i class="ti ti-calendar me-1"></i>{{ $item['history']->changed_at?->format('d M Y') }}
                </p>
                @if($item['history']->notes)
                    <p class="text-muted small fst-italic mt-1 mb-0">{{ $item['history']->notes }}</p>
                @endif
            @elseif($item['pending'])
                <p class="text-muted small mb-0">—</p>
            @endif
        </div>
    </div>
    @endforeach
</div>

@once
@push('styles')
<style>
.workflow-timeline { position: relative; padding: 0; }
.timeline-item { display: flex; gap: 1rem; padding-bottom: 1.5rem; position: relative; }
.timeline-item:not(:last-child)::before {
    content: ''; position: absolute; left: 19px; top: 40px; bottom: 0;
    width: 2px; background: #e5e7eb;
}
.timeline-dot {
    flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #9ca3af; z-index: 1;
}
.timeline-item--completed .timeline-dot { background: #22c55e; border-color: #22c55e; color: #fff; }
.timeline-item--current .timeline-dot {
    background: var(--dot-color, #3B82F6); border-color: var(--dot-color, #3B82F6);
    color: #fff; box-shadow: 0 0 0 4px rgba(59,130,246,.15);
}
.timeline-item--pending .timeline-dot { background: #f9fafb; border-color: #d1d5db; color: #d1d5db; }
.timeline-content { flex: 1; padding-top: 8px; }
</style>
@endpush
@endonce
