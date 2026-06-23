@props([
    'patient' => null,
    'screeningFields' => [],
])

@if(!empty($screeningFields))
@include('pages.patients.partials.clinical-fallback-styles')
<x-card :title="__('patients.sections.screening')" class="mt-3">
    <p class="text-muted small mb-3">{{ __('patients.sections.screening_hint') }}</p>
    <div class="row g-3">
        @foreach($screeningFields as $fieldKey => $fieldDef)
            @php
                $inputName = 'screening_'.$fieldKey;
                $savedValue = old($inputName, $patient?->screening($fieldKey));
                $inputType = $fieldDef['type'] ?? 'text';
                $colClass = in_array($inputType, ['textarea', 'clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'expandable_checklist', 'medical_history_screening', 'imaging_findings']) ? 'col-12' : 'col-md-6';
                $phase = $fieldDef['phase'] ?? 'screening';
                $phaseStyle = config("patient_clinical.phases.{$phase}.background", '#f8f9fa');
            @endphp
            <div class="{{ $colClass }}">
                <div class="clinical-field-shell" style="--clinical-phase-bg: {{ $phaseStyle }};">
                    <label class="form-label fw-semibold small mb-1">{{ $fieldDef['label'] }}</label>
                    @if($inputType === 'clinical_aud')
                        <x-clinical-aud-input
                            :name-prefix="$inputName"
                            :saved-value="$savedValue"
                            :metrics-keys="\App\Support\ClinicalCompositeFields::metricsKeysFromDefinition($fieldDef)"
                            :with-status="(bool) ($fieldDef['with_status'] ?? true)"
                            :allow-add-rows="(bool) ($fieldDef['allow_add_metrics'] ?? true)"
                        />
                    @elseif($inputType === 'clinical_speech_followup')
                        <x-clinical-speech-followup-input :name-prefix="$inputName" :saved-value="$savedValue" />
                    @elseif($inputType === 'clinical_speech')
                        <x-clinical-speech-input :name-prefix="$inputName" :saved-value="$savedValue" />
                    @elseif($inputType === 'expandable_checklist')
                        <x-expandable-checklist-input
                            :name-prefix="$inputName"
                            :saved-value="$savedValue"
                            :options="$fieldDef['options'] ?? []"
                            :allow-add="(bool) ($fieldDef['allow_add_options'] ?? true)"
                            :field-definition="$fieldDef"
                        />
                    @elseif($inputType === 'medical_history_screening')
                        <x-medical-history-screening-input
                            :name-prefix="$inputName"
                            :saved-value="$savedValue"
                            :lists="$fieldDef['lists'] ?? []"
                        />
                    @elseif($inputType === 'imaging_findings')
                        <x-imaging-findings-input
                            :name-prefix="$inputName"
                            :saved-value="$savedValue"
                            :ct-options="$fieldDef['ct_options'] ?? []"
                            :mri-options="$fieldDef['mri_options'] ?? []"
                        />
                    @elseif($inputType === 'textarea')
                        <textarea name="{{ $inputName }}" class="form-control form-control-sm" rows="3">{{ $savedValue }}</textarea>
                    @elseif($inputType === 'select' && isset($fieldDef['options']))
                        <select name="{{ $inputName }}" class="form-select form-select-sm">
                            <option value="">— {{ __('common.select') }} —</option>
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
