@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'icon' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $errors->has($name);
@endphp

<div class="form-group-admin">
    <label for="{{ $inputId }}" class="form-group-admin__label">
        {{ $label }}
        @if($required)
            <span class="required">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea
            id="{{ $inputId }}"
            name="{{ $name }}"
            class="form-group-admin__input {{ $hasError ? 'is-invalid' : '' }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            rows="{{ $attributes->get('rows', 4) }}"
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select
            id="{{ $inputId }}"
            name="{{ $name }}"
            class="form-group-admin__input {{ $hasError ? 'is-invalid' : '' }}"
            {{ $required ? 'required' : '' }}
        >
            {{ $slot }}
        </select>
    @else
        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="form-group-admin__input {{ $hasError ? 'is-invalid' : '' }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->except(['class', 'rows']) }}
        >
    @endif

    @if($hint)
        <div class="form-group-admin__hint">{{ $hint }}</div>
    @endif

    @error($name)
        <div class="form-group-admin__error">{{ $message }}</div>
    @enderror
</div>
