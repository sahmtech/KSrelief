@props(['transportationLocation' => null])

@php
    $locationTypes = ['hotel', 'hospital', 'airport', 'other'];
@endphp

<x-form-input :label="__('settings.fields.name')" name="name" :value="old('name', $transportationLocation?->name)" required />
<x-form-input :label="__('settings.fields.type')" name="type" type="select" :value="old('type', $transportationLocation?->type)" required>
    <option value="">{{ __('settings.fields.type') }}</option>
    @foreach($locationTypes as $type)
        <option value="{{ $type }}" @selected(old('type', $transportationLocation?->type) === $type)>
            {{ __('settings.transportation_types.'.$type) }}
        </option>
    @endforeach
</x-form-input>
<x-form-input :label="__('settings.fields.description')" name="description" type="textarea" :value="old('description', $transportationLocation?->description)" />
<x-settings.status-select :value="old('status', $transportationLocation?->status?->value ?? 'active')" />
