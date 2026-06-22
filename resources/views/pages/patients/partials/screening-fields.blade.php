@props([
    'patient' => null,
    'screeningFields' => [],
])

@if(!empty($screeningFields))
<x-card :title="__('patients.sections.screening')" class="mt-3">
    <p class="text-muted small mb-3">{{ __('patients.sections.screening_hint') }}</p>
    <div class="row g-3">
        @foreach($screeningFields as $fieldKey => $fieldDef)
            @php
                $inputName = 'screening_'.$fieldKey;
                $savedValue = old($inputName, $patient?->screening($fieldKey));
                $inputType = $fieldDef['type'] ?? 'text';
                $colClass = in_array($inputType, ['textarea']) ? 'col-12' : 'col-md-6';
                $phase = $fieldDef['phase'] ?? 'screening';
                $phaseStyle = config("patient_clinical.phases.{$phase}.background", '#f8f9fa');
            @endphp
            <div class="{{ $colClass }}">
                <div class="clinical-field-shell" style="--clinical-phase-bg: {{ $phaseStyle }};">
                    <label class="form-label fw-semibold small mb-1">{{ $fieldDef['label'] }}</label>
                    @if($inputType === 'textarea')
                        <textarea name="{{ $inputName }}" class="form-control form-control-sm" rows="3">{{ $savedValue }}</textarea>
                    @elseif($inputType === 'select' && isset($fieldDef['options']))
                        <select name="{{ $inputName }}" class="form-select form-select-sm">
                            <option value="">—</option>
                            @foreach($fieldDef['options'] as $optVal => $optLabel)
                                <option value="{{ $optVal }}" @selected((string) $savedValue === (string) $optVal)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    @elseif($inputType === 'url')
                        <input type="url" name="{{ $inputName }}" class="form-control form-control-sm" value="{{ $savedValue }}" placeholder="{{ __('workflow.links.drive_placeholder') }}">
                    @else
                        <input type="text" name="{{ $inputName }}" class="form-control form-control-sm" value="{{ $savedValue }}">
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-card>
@endif
