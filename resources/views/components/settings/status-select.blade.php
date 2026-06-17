@props([
    'name' => 'status',
    'label' => null,
    'value' => null,
    'required' => true,
    'statuses' => null,
])

@php
    $statuses = $statuses ?? \App\Enums\SettingStatus::cases();
    $label = $label ?? __('settings.fields.status');
@endphp

<x-form-input :label="$label" :name="$name" type="select" :value="$value" :required="$required">
    @foreach($statuses as $status)
        <option value="{{ $status->value }}" @selected(old($name, $value) === $status->value)>
            {{ $status->label() }}
        </option>
    @endforeach
</x-form-input>
