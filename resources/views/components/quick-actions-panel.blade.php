@props([
    'actions' => [],
])

@if(count($actions))
<div class="dashboard-quick-actions mb-4">
    <div class="dashboard-quick-actions__header">
        <h6 class="dashboard-quick-actions__title">{{ __('dashboard.quick_actions.title') }}</h6>
        <p class="dashboard-quick-actions__subtitle">{{ __('dashboard.quick_actions.subtitle') }}</p>
    </div>
    <div class="row g-2">
        @foreach($actions as $action)
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ $action['route'] }}" class="dashboard-quick-actions__item">
                <span class="dashboard-quick-actions__icon"><i class="ti {{ $action['icon'] }}"></i></span>
                <span class="dashboard-quick-actions__label">{{ $action['label'] }}</span>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endif
