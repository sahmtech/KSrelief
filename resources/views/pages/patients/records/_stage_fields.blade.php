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
                {{ __('workflow.title') }} — {{ ucfirst(str_replace('_', ' ', $stageCode)) }}
            </h6>
            @if(!empty($phaseStyle['label']))
                <span class="badge clinical-phase-badge" style="background: {{ $phaseStyle['background'] ?? '#f8f9fa' }};">{{ __($phaseStyle['label'] ?? 'workflow.phases.pre_op') }}</span>
            @endif
        </div>
        <div class="row g-3">
            @foreach($stageFields as $fieldKey => $fieldDef)
            @php
                $inputName   = 'field_' . $fieldKey;
                $savedValue  = old($inputName, isset($record) ? $record->field($fieldKey) : null);
                $isRequired  = $fieldDef['required'] ?? false;
                $inputType   = $fieldDef['type'] ?? 'text';
                $label       = $fieldDef['label'] ?? ucfirst($fieldKey);
                $colClass    = in_array($inputType, ['textarea']) ? 'col-12' : 'col-md-6';
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
                    <select name="{{ $inputName }}" class="form-select form-select-sm" {{ $isRequired ? 'required' : '' }}>
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

                @elseif($inputType === 'textarea')
                    <textarea name="{{ $inputName }}"
                              class="form-control form-control-sm"
                              rows="3"
                              {{ $isRequired ? 'required' : '' }}>{{ $savedValue }}</textarea>

                @elseif($inputType === 'select' && isset($fieldDef['options']))
                    <select name="{{ $inputName }}" class="form-select form-select-sm" {{ $isRequired ? 'required' : '' }}>
                        <option value="">—</option>
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
