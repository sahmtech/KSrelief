@props([
    'items' => [],
])

<nav aria-label="breadcrumb">
    <ol class="breadcrumb-admin">
        <li class="breadcrumb-admin__item">
            <a href="{{ route('dashboard') }}"><i class="ti ti-home" style="font-size: 0.875rem;"></i></a>
        </li>
        @foreach($items as $item)
            <li class="breadcrumb-admin__item {{ $loop->last ? 'active' : '' }}" {{ $loop->last ? 'aria-current=page' : '' }}>
                @if(!$loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
            </li>
        @endforeach
    </ol>
</nav>
