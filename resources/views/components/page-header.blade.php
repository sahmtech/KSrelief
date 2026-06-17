@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div class="page-header">
    @if(count($breadcrumbs))
        <div class="page-header__breadcrumb">
            <x-breadcrumb :items="$breadcrumbs" />
        </div>
    @endif

    <div class="page-header__row">
        <div>
            <h1 class="page-header__title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="page-header__actions">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
