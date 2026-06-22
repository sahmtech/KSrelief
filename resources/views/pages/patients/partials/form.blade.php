@props([
    'patient' => null,
    'campaigns' => [],
    'selectedCampaignId' => null,
    'eligibilityStatuses' => [],
    'patientStages' => [],
    'genders' => [],
    'admissionStatuses' => [],
    'recordStatuses' => [],
    'screeningFields' => [],
    'surgicalSides' => [],
])

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('patients.sections.basic')">
            @include('pages.patients.partials.photo-upload', ['patient' => $patient, 'inputId' => 'patientPhotoInput'.($patient?->id ?? 'New')])

            <x-form-input :label="__('patients.fields.patient_name')" name="patient_name" :value="old('patient_name', $patient?->patient_name)" :placeholder="__('patients.placeholders.patient_name')" required />
            @if($patient?->file_number)
                <div class="mb-3">
                    <label class="form-group-admin__label">{{ __('patients.fields.file_number') }}</label>
                    <div class="form-control form-control-sm bg-light">
                        <code>{{ $patient->file_number }}</code>
                    </div>
                    <div class="form-text">{{ __('patients.hints.file_number_auto') }}</div>
                </div>
            @else
                <div class="mb-3">
                    <label class="form-group-admin__label">{{ __('patients.fields.file_number') }}</label>
                    <div class="form-control form-control-sm bg-light text-muted">{{ __('patients.hints.file_number_generated_on_save') }}</div>
                </div>
            @endif
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('patients.fields.date_of_birth')" name="date_of_birth" type="date" :value="old('date_of_birth', $patient?->date_of_birth?->format('Y-m-d'))" required />
                </div>
                <div class="col-md-6 ps-md-2">
                    <x-form-input :label="__('patients.fields.gender')" name="gender" type="select" required>
                        <option value="">{{ __('patients.fields.gender') }}</option>
                        @foreach($genders as $gender)
                            <option value="{{ $gender->value }}" @selected(old('gender', $patient?->gender?->value) === $gender->value)>
                                {{ $gender->label() }}
                            </option>
                        @endforeach
                    </x-form-input>
                </div>
            </div>
            @if($patient)
                <div class="text-muted" style="font-size: 0.8125rem;">
                    <i class="ti ti-info-circle me-1"></i>
                    {{ __('patients.fields.age') }}: {{ $patient->ageLabel() }}
                </div>
            @endif
            <div class="row g-0 mt-2">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('patients.fields.height_cm')" name="height_cm" type="number" step="0.1" min="20" max="250" :value="old('height_cm', $patient?->height_cm)" :placeholder="__('patients.placeholders.height_cm')" />
                </div>
                <div class="col-md-6 ps-md-2">
                    <x-form-input :label="__('patients.fields.weight_kg')" name="weight_kg" type="number" step="0.1" min="0.5" max="500" :value="old('weight_kg', $patient?->weight_kg)" :placeholder="__('patients.placeholders.weight_kg')" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('patients.sections.campaign')">
            <x-form-input :label="__('patients.fields.campaign')" name="campaign_id" type="select" required>
                <option value="">{{ __('patients.placeholders.select_campaign') }}</option>
                @foreach($campaigns as $campaign)
                    <option value="{{ $campaign->id }}" @selected((string) old('campaign_id', $patient?->campaign_id ?? $selectedCampaignId) === (string) $campaign->id)>
                        {{ $campaign->name }}
                        @if($campaign->country)
                            — {{ $campaign->country->localizedName() }}
                        @endif
                    </option>
                @endforeach
            </x-form-input>
        </x-card>

        <x-card :title="__('patients.sections.contact')" class="mt-3">
            <x-form-input :label="__('patients.fields.contact_number')" name="contact_number" :value="old('contact_number', $patient?->contact_number)" :placeholder="__('patients.placeholders.contact_number')" />
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('patients.sections.medical')">
            <x-form-input :label="__('patients.fields.eligibility_status')" name="eligibility_status_id" type="select" required>
                <option value="">{{ __('patients.placeholders.select_eligibility') }}</option>
                @foreach($eligibilityStatuses as $status)
                    <option value="{{ $status->id }}" @selected((string) old('eligibility_status_id', $patient?->eligibility_status_id) === (string) $status->id)>
                        {{ $status->name }}
                    </option>
                @endforeach
            </x-form-input>
            <x-form-input :label="__('patients.fields.approval_reason')" name="approval_reason" type="textarea" :value="old('approval_reason', $patient?->approval_reason)" />
            <div class="row g-0">
                <div class="col-md-4 pe-md-2">
                    <x-form-input :label="__('patients.fields.surgery_day_number')" name="surgery_day_number" type="number" min="1" max="99" :value="old('surgery_day_number', $patient?->surgery_day_number)" />
                </div>
                <div class="col-md-4 px-md-1">
                    <x-form-input :label="__('patients.fields.rank')" name="rank" type="number" min="1" :value="old('rank', $patient?->rank)" />
                </div>
                <div class="col-md-4 ps-md-2">
                    <x-form-input :label="__('patients.fields.surgical_side')" name="surgical_side" type="select">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($surgicalSides as $side)
                            <option value="{{ $side }}" @selected(old('surgical_side', $patient?->surgical_side) === $side)>{{ __('workflow.sides.'.$side) }}</option>
                        @endforeach
                    </x-form-input>
                </div>
            </div>
            @if($patient)
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('patients.fields.current_stage') }}</label>
                    <div class="d-flex align-items-center gap-2">
                        @if($patient->currentStage)
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background-color: {{ $patient->currentStage->color ?? '#3B82F6' }}; color: #fff;">
                                {{ $patient->currentStage->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                        @can('changeStage', $patient)
                            <a href="{{ route('patients.show', $patient) }}#workflow" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-transfer me-1"></i>{{ __('workflow.change_stage') }}
                            </a>
                        @endcan
                    </div>
                    <div class="form-text">{{ __('workflow.messages.stage_change_via_workflow') }}</div>
                </div>
            @else
                <x-form-input :label="__('patients.fields.current_stage')" name="current_stage_id" type="select">
                    <option value="">{{ __('patients.placeholders.select_stage') }}</option>
                    @foreach($patientStages as $stage)
                        <option value="{{ $stage->id }}" @selected((string) old('current_stage_id') === (string) $stage->id)>
                            {{ $stage->name }}
                        </option>
                    @endforeach
                </x-form-input>
            @endif
            <x-form-input :label="__('patients.fields.admission_status')" name="admission_status" type="select">
                @foreach($admissionStatuses as $status)
                    <option value="{{ $status->value }}" @selected(old('admission_status', $patient?->admission_status?->value ?? 'not_admitted') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form-input>
            @if($patient)
                <x-form-input :label="__('patients.fields.record_status')" name="status" type="select">
                    @foreach($recordStatuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $patient?->status?->value ?? 'active') === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </x-form-input>
            @endif
            <x-form-input :label="__('patients.fields.notes')" name="notes" type="textarea" :value="old('notes', $patient?->notes)" :placeholder="__('patients.placeholders.notes')" />
        </x-card>
    </div>

    <div class="col-12">
        @include('pages.patients.partials.screening-fields', [
            'patient' => $patient,
            'screeningFields' => $screeningFields,
        ])
    </div>

    <div class="col-lg-6">
        <x-card :title="__('patients.sections.attachments')">
            <x-form-input :label="__('patients.fields.attachment')" name="attachments[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx" />

            @if($patient && $patient->attachments->isNotEmpty())
                <div class="mt-3">
                    <div class="fw-medium mb-2" style="font-size: 0.875rem;">{{ __('patients.sections.attachments') }}</div>
                    <ul class="list-group list-group-flush border rounded">
                        @foreach($patient->attachments as $attachment)
                            <li class="list-group-item d-flex align-items-center justify-content-between gap-2 py-2">
                                <div class="min-w-0">
                                    <div class="text-truncate fw-medium" style="font-size: 0.875rem;">{{ $attachment->original_name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $attachment->humanFileSize() }} · {{ $attachment->created_at->format('Y-m-d') }}</div>
                                </div>
                                <div class="d-flex gap-1 flex-shrink-0">
                                    <a href="{{ route('patients.attachments.download', [$patient, $attachment]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('patients.actions.download') }}">
                                        <i class="ti ti-download"></i>
                                    </a>
                                    @can('update', $patient)
                                        <form method="POST" action="{{ route('patients.attachments.destroy', [$patient, $attachment]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="{{ __('patients.messages.confirm_remove_attachment') }}" title="{{ __('patients.actions.remove_attachment') }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-card>
    </div>
</div>
