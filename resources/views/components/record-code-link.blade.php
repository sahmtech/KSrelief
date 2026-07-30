@props([
    'href' => '#',
    'code' => null,
    'color' => null,
    'codeClass' => null,
])

@if(filled($code))
    @php
        $codeStyle = filled($color)
            ? 'color: '.$color.'; border: 1px solid '.$color.'33; background-color: '.$color.'14;'
            : '';
    @endphp
    <a
        href="{{ $href }}"
        class="record-code-link text-decoration-none{{ filled($color) ? ' record-code-link--accent' : '' }}"
    >
        <code
            @class([$codeClass => filled($codeClass)])
            @if(filled($color)) style="{{ $codeStyle }}" @endif
        >{{ $code }}</code>
    </a>
@else
    <span class="text-muted">—</span>
@endif
