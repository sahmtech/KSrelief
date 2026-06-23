@if(!empty($stageFields))
@include('pages.patients.partials.clinical-fallback-styles')
@php
    $phaseCode = app(\App\Services\MedicalRecordService::class)->phaseForStage($stageCode);
    $phaseStyle = config("patient_clinical.phases.{$phaseCode}", []);
@endphp
<div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#f8f9fa' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#374151' }};">
    <div class="card-body">
        <div class="clinical-phase-panel__header mb-3">
            <h6 class="mb-0">
                <i class="ti ti-clipboard-list me-2"></i>
                {{ __('workflow.title') }} —
                @if(($stageCode ?? '') === 'pre_operation')
                    {{ __('patients.sections.screening') }}
                @else
                    {{ ucfirst(str_replace('_', ' ', $stageCode)) }}
                @endif
            </h6>
            @if(!empty($phaseStyle['label']))
                <span class="badge clinical-phase-badge" style="background: {{ $phaseStyle['background'] ?? '#f8f9fa' }};">{{ __($phaseStyle['label'] ?? 'workflow.phases.pre_op') }}</span>
            @endif
        </div>
        <div class="row g-3">
            @foreach($stageFields as $fieldKey => $fieldDef)
            @php
                $inputName   = 'field_' . $fieldKey;
                $prefill = null;
                if (! isset($record) && isset($patient) && ($stageCode ?? '') === 'pre_operation') {
                    $prefill = $patient->screening($fieldKey);
                }
                $savedValue  = old($inputName, isset($record) ? $record->field($fieldKey) : $prefill);
                $isRequired  = $fieldDef['required'] ?? false;
                $inputType   = $fieldDef['type'] ?? 'text';
                $label       = $fieldDef['label'] ?? ucfirst($fieldKey);
                $colClass    = in_array($inputType, ['textarea', 'clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'expandable_checklist', 'medical_history_screening', 'imaging_findings']) ? 'col-12' : 'col-md-6';
                $memberRole  = $fieldDef['member_role'] ?? null;
                $members     = $memberRole && isset($teamMembers[$memberRole . 's'])
                    ? $teamMembers[$memberRole . 's']
                    : collect();
            @endphp
            <div class="{{ $colClass }}">
                <label class="form-label fw-semibold small">
                    {{ $label }}
                    @if($isRequired) <span class="text-danger">*</span> @endif
                </label>

                @if($inputType === 'member_select')
                    @php
                        $memberSelectClass = 'form-select form-select-sm';
                        if (($stageCode ?? '') === 'operation') {
                            if ($fieldKey === 'surgeon') {
                                $memberSelectClass .= ' operation-surgeon-select';
                            } elseif ($fieldKey === 'specialist') {
                                $memberSelectClass .= ' operation-specialist-select';
                            }
                        }
                    @endphp
                    <select name="{{ $inputName }}"
                            id="{{ $inputName }}"
                            class="{{ $memberSelectClass }}"
                            autocomplete="off"
                            {{ $isRequired ? 'required' : '' }}>
                        <option value="">— {{ __('common.select') }} —</option>
                        @forelse($members as $member)
                            <option value="{{ $member->id }}" {{ (string) $savedValue === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->full_name }}
                                @if($member->specialty)
                                    — {{ $member->specialty->name }}
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>{{ __('workflow.messages.no_campaign_members') }}</option>
                        @endforelse
                    </select>

                @elseif($inputType === 'company_select')
                    @php $companies = $implantCompanies ?? collect(); @endphp
                    <select name="{{ $inputName }}" id="implantCompanySelect" class="form-select form-select-sm operation-company-select" {{ $isRequired ? 'required' : '' }}
                            data-electrode-target="electrodeTypeSelect"
                            data-electrode-url="{{ $electrodeTypesUrl ?? '' }}">
                        <option value="">— {{ __('common.select') }} —</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}"
                                    data-color="{{ $company->color }}"
                                    style="color: {{ $company->color }}; font-weight: 600;"
                                    {{ (string) $savedValue === (string) $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>

                @elseif($inputType === 'electrode_select')
                    @php $electrodes = $implantElectrodeTypes ?? collect(); @endphp
                    <select name="{{ $inputName }}" id="electrodeTypeSelect" class="form-select form-select-sm operation-electrode-select" {{ $isRequired ? 'required' : '' }}>
                        <option value="">— {{ __('common.select') }} —</option>
                        @foreach($electrodes as $electrode)
                            <option value="{{ $electrode->id }}" {{ (string) $savedValue === (string) $electrode->id ? 'selected' : '' }}>
                                {{ $electrode->name }}
                            </option>
                        @endforeach
                    </select>

                @elseif($inputType === 'insertion_approach_select')
                    @php $approaches = $insertionApproaches ?? collect(); @endphp
                    <select name="{{ $inputName }}" class="form-select form-select-sm" {{ $isRequired ? 'required' : '' }}>
                        <option value="">— {{ __('common.select') }} —</option>
                        @foreach($approaches as $approach)
                            <option value="{{ $approach->id }}" {{ (string) $savedValue === (string) $approach->id ? 'selected' : '' }}>
                                {{ $approach->name }}
                            </option>
                        @endforeach
                    </select>

                @elseif($inputType === 'clinical_aud')
                    <x-clinical-aud-input
                        :name-prefix="$inputName"
                        :saved-value="$savedValue"
                        :metrics-keys="\App\Support\ClinicalCompositeFields::metricsKeysFromDefinition($fieldDef)"
                        :with-status="(bool) ($fieldDef['with_status'] ?? true)"
                        :allow-add-rows="(bool) ($fieldDef['allow_add_metrics'] ?? true)"
                    />

                @elseif($inputType === 'clinical_speech_followup')
                    <x-clinical-speech-followup-input
                        :name-prefix="$inputName"
                        :saved-value="$savedValue"
                        :allow-add-rows="(bool) ($fieldDef['allow_add_metrics'] ?? true)"
                    />

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
                    <textarea name="{{ $inputName }}"
                              class="form-control form-control-sm"
                              rows="3"
                              {{ $isRequired ? 'required' : '' }}>{{ $savedValue }}</textarea>

                @elseif($inputType === 'select' && isset($fieldDef['options']))
                    <select name="{{ $inputName }}" class="form-select form-select-sm" {{ $isRequired ? 'required' : '' }}>
                        <option value="">— {{ __('common.select') }} —</option>
                        @foreach($fieldDef['options'] as $optVal => $optLabel)
                            <option value="{{ $optVal }}" {{ $savedValue == $optVal ? 'selected' : '' }}>
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </select>

                @elseif($inputType === 'date')
                    <input type="date" name="{{ $inputName }}"
                           class="form-control form-control-sm"
                           value="{{ $savedValue }}"
                           {{ $isRequired ? 'required' : '' }}>

                @elseif($inputType === 'time')
                    <input type="time" name="{{ $inputName }}"
                           class="form-control form-control-sm"
                           value="{{ $savedValue }}"
                           {{ $isRequired ? 'required' : '' }}>

                @elseif($inputType === 'url')
                    <input type="url" name="{{ $inputName }}"
                           class="form-control form-control-sm"
                           value="{{ $savedValue }}"
                           placeholder="{{ __('workflow.links.drive_placeholder') }}"
                           {{ $isRequired ? 'required' : '' }}>

                @elseif($inputType === 'number')
                    <input type="number" name="{{ $inputName }}"
                           class="form-control form-control-sm"
                           step="0.01" min="0"
                           value="{{ $savedValue }}"
                           {{ $isRequired ? 'required' : '' }}>

                @else
                    <input type="text" name="{{ $inputName }}"
                           class="form-control form-control-sm"
                           value="{{ $savedValue }}"
                           {{ $isRequired ? 'required' : '' }}>
                @endif
            </div>
            @endforeach
        </div>

        @if($stageCode === 'admission')
        <div class="mt-3 pt-3 border-top">
            <label class="form-label fw-semibold small">{{ __('workflow.fields.admission_attachments') }}</label>
            <input type="file" name="admission_attachments[]" class="form-control form-control-sm" multiple
                   accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx">
            <div class="form-text">{{ __('workflow.fields.admission_attachments_hint') }}</div>
        </div>
        @endif
    </div>
</div>
@endif
