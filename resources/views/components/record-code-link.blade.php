@props([
    'href' => '#',
    'code' => null,
])

@if(filled($code))
    <a href="{{ $href }}" class="record-code-link text-decoration-none">
        <code>{{ $code }}</code>
    </a>
@else
    <span class="text-muted">—</span>
@endif
