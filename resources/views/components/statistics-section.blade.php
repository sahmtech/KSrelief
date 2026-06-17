@props([
    'title',
    'subtitle' => null,
])

<div class="dashboard-kpi-section mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h6 class="text-muted text-uppercase mb-0" style="font-size: 0.75rem; letter-spacing: 0.05em;">{{ $title }}</h6>
            @if($subtitle)<p class="small text-muted mb-0 mt-1">{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)<div>{{ $actions }}</div>@endisset
    </div>
    <div class="row g-3">
        {{ $slot }}
    </div>
</div>
