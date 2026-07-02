@props([
    'namePrefix',
    'savedValue' => null,
    'label' => null,
])

@php
    $savedValue = old($namePrefix, $savedValue);
    $savedValue = is_string($savedValue) ? trim($savedValue) : '';
    $isChecked = $savedValue === 'yes';
@endphp

<div class="form-check mb-0">
    <input type="hidden" name="{{ $namePrefix }}" value="no">
    <input type="checkbox"
           class="form-check-input"
           name="{{ $namePrefix }}"
           id="{{ $namePrefix }}"
           value="yes"
           @checked($isChecked)>
    @if(filled($label))
        <label class="form-check-label small" for="{{ $namePrefix }}">{{ $label }}</label>
    @endif
</div>
