@props([
    'value' => null,
    'type' => null,
])

@php
    $presented = \App\Support\ClinicalValuePresenter::present($value, $type);
@endphp

@if($presented['is_link'])
    <a href="{{ $presented['url'] }}" target="_blank" rel="noopener noreferrer" class="clinical-link text-break">
        <i class="ti ti-{{ $presented['icon'] }} me-1"></i>{{ $presented['label'] }}
    </a>
@else
    <span class="text-break">{{ $presented['text'] }}</span>
@endif
