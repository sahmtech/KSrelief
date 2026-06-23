@props(['value' => null, 'fieldDefinition' => []])

@php
    $text = \App\Support\ScreeningFieldSupport::presentExpandableChecklist($value, is_array($fieldDefinition) ? $fieldDefinition : []);
@endphp

<span class="text-break">{{ $text }}</span>
