@include('pages.patients.partials.clinical-fallback-styles')

@php
    $phaseStyle = config('patient_clinical.phases.intra_op', []);
    $recordModel = $record ?? null;
    $showTemplateActions = ! isset($record) && ($enableOperationTemplateActions ?? false);
    $showQuickFill = ! isset($record);

    $clinicalSelectOptionUrl = route('patients.records.clinical-select-options.store', $patient);
    $electrodeTypesUrl = $electrodeTypesUrl ?? route('patients.records.electrode-types', $patient);
    $quickFillUrl = $operationQuickFillUrl ?? '';

    $legacyInsertionNote = $recordModel?->field('insertion_depth_note');
    $legacyImpedance = $recordModel?->field('impedance_testing');
    $legacyIntraOpFindings = is_string($recordModel?->field('intra_op_findings'))
        ? $recordModel->field('intra_op_findings')
        : null;

    $operationDate = old('field_operation_date', $recordModel?->field('operation_date') ?? date('Y-m-d'));
    $surgeon = old('field_surgeon', $recordModel?->field('surgeon'));
    $implantCompanyId = old('field_implant_company_id', $recordModel?->field('implant_company_id'));
    $electrodeTypeId = old('field_electrode_type_id', $recordModel?->field('electrode_type_id'));
    $insertionApproachId = old('field_insertion_approach_id', $recordModel?->field('insertion_approach_id'));
    $insertionDepth = old('field_insertion_depth', $recordModel?->field('insertion_depth'));
    $timeInSurgery = old('field_time_in_surgery', $recordModel?->field('time_in_surgery'));
    $timeOutSurgery = old('field_time_out_surgery', $recordModel?->field('time_out_surgery'));
    $audioTest = old('field_audio_test', $recordModel?->field('audio_test'));
    $intraOpFindings = old('field_intra_op_findings', $recordModel?->field('intra_op_findings'));
    $operationNotes = old('field_operation_notes', $recordModel?->field('operation_notes'));

    $doctors = $teamMembers['doctors'] ?? collect();
    $companies = $implantCompanies ?? collect();
    $quickFillCompanies = ($campaignDefaultCompanies ?? collect())->isNotEmpty()
        ? $campaignDefaultCompanies
        : $companies;
    $electrodes = $implantElectrodeTypes ?? collect();
    $approaches = $insertionApproaches ?? collect();

    $operationFieldDefs = $stageFields ?? config('patient_clinical.stage_fields.operation', []);
    $fieldRequired = fn (string $key): bool => (bool) ($operationFieldDefs[$key]['required'] ?? false);
@endphp

<div class="operation-stage-fields"
     data-operation-fields
     data-electrode-url="{{ $electrodeTypesUrl }}">

    <div class="follow-up-form-header clinical-phase-panel mb-3" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#B6D7A8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6AA84F' }};">
        <div class="follow-up-form-header__inner">
            <h6 class="mb-0 fw-semibold">
                <i class="ti ti-clipboard-list me-2"></i>
                {{ __('workflow.title') }} — {{ __('workflow.phases.intra_op') }}
            </h6>
            @if($showTemplateActions)
                <div class="d-none" aria-hidden="true">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                                data-operation-load-campaign-defaults
                                class="btn btn-outline-success btn-sm"
                                @disabled(!($hasCampaignOperationDefaults ?? false))>
                            <i class="ti ti-building-hospital me-1"></i>{{ __('workflow.operation.load_campaign_defaults') }}
                        </button>
                        <button type="button"
                                data-operation-load-template
                                class="btn btn-outline-primary btn-sm"
                                @disabled(!($hasOperationDefaults ?? false))>
                            <i class="ti ti-template me-1"></i>{{ __('workflow.operation.load_template') }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($showQuickFill)
        <ul class="nav nav-tabs operation-form-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button type="button"
                        class="nav-link"
                        role="tab"
                        data-operation-tab="manual"
                        aria-selected="false">
                    <i class="ti ti-forms me-1"></i>{{ __('workflow.operation.tabs.manual_entry') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button type="button"
                        class="nav-link active"
                        role="tab"
                        data-operation-tab="quick_fill"
                        aria-selected="true">
                    <i class="ti ti-bolt me-1"></i>{{ __('workflow.operation.tabs.quick_fill') }}
                </button>
            </li>
        </ul>
    @endif

    <div data-operation-tab-panel="manual" hidden>
        <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#B6D7A8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6AA84F' }};">
            <div class="clinical-phase-panel__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.operation_date') }}
                            @if($fieldRequired('operation_date'))<span class="text-danger">*</span>@endif
                        </label>
                        <input type="date" name="field_operation_date" class="form-control" value="{{ $operationDate }}" @required($fieldRequired('operation_date'))>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.surgeon') }}
                            @if($fieldRequired('surgeon'))<span class="text-danger">*</span>@endif
                        </label>
                        <select name="field_surgeon" id="field_surgeon" class="form-select operation-surgeon-select" autocomplete="off" @required($fieldRequired('surgeon'))>
                            <option value="">— {{ __('common.select') }} —</option>
                            @forelse($doctors as $member)
                                <option value="{{ $member->id }}" @selected((string) $surgeon === (string) $member->id)>
                                    {{ $member->full_name }}@if($member->specialty) — {{ $member->specialty->name }}@endif
                                </option>
                            @empty
                                <option value="" disabled>{{ __('workflow.messages.no_campaign_members') }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.implant_company') }}
                            @if($fieldRequired('implant_company_id'))<span class="text-danger">*</span>@endif
                        </label>
                        <select name="field_implant_company_id"
                                id="implantCompanySelect"
                                class="form-select operation-company-select"
                                data-electrode-target="electrodeTypeSelect"
                                data-electrode-url="{{ $electrodeTypesUrl }}"
                                @required($fieldRequired('implant_company_id'))>
                            <option value="">— {{ __('common.select') }} —</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                        data-color="{{ $company->color }}"
                                        style="color: {{ $company->color }}; font-weight: 600;"
                                        @selected((string) $implantCompanyId === (string) $company->id)>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.electrode_type') }}
                            @if($fieldRequired('electrode_type_id'))<span class="text-danger">*</span>@endif
                        </label>
                        <select name="field_electrode_type_id" id="electrodeTypeSelect" class="form-select operation-electrode-select" @required($fieldRequired('electrode_type_id'))>
                            <option value="">— {{ __('common.select') }} —</option>
                            @foreach($electrodes as $electrode)
                                <option value="{{ $electrode->id }}" @selected((string) $electrodeTypeId === (string) $electrode->id)>
                                    {{ $electrode->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.insertion_approach') }}
                            @if($fieldRequired('insertion_approach_id'))<span class="text-danger">*</span>@endif
                        </label>
                        <select name="field_insertion_approach_id" id="insertionApproachSelect" class="form-select operation-approach-select" @required($fieldRequired('insertion_approach_id'))>
                            <option value="">— {{ __('common.select') }} —</option>
                            @foreach($approaches as $approach)
                                <option value="{{ $approach->id }}" @selected((string) $insertionApproachId === (string) $approach->id)>
                                    {{ $approach->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pre-op-section-divider pt-3 mt-2 mb-3">
                    <x-operation-insertion-depth-input
                        name-prefix="field_insertion_depth"
                        :saved-value="$insertionDepth"
                        :legacy-note="$legacyInsertionNote"
                        :clinical-select-option-url="$clinicalSelectOptionUrl"
                        :required="$fieldRequired('insertion_depth')"
                    />
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.time_in_surgery') }}
                            @if($fieldRequired('time_in_surgery'))<span class="text-danger">*</span>@endif
                        </label>
                        <input type="time" name="field_time_in_surgery" class="form-control" value="{{ $timeInSurgery }}" @required($fieldRequired('time_in_surgery'))>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small mb-1">
                            {{ __('workflow.fields.time_out_surgery') }}
                            @if($fieldRequired('time_out_surgery'))<span class="text-danger">*</span>@endif
                        </label>
                        <input type="time" name="field_time_out_surgery" class="form-control" value="{{ $timeOutSurgery }}" @required($fieldRequired('time_out_surgery'))>
                    </div>
                </div>

                <div class="pre-op-section-divider pt-3 mt-2 mb-3">
                    <x-operation-audio-test-input
                        name-prefix="field_audio_test"
                        :saved-value="$audioTest"
                        :legacy-impedance="$legacyImpedance"
                        :required="$fieldRequired('audio_test')"
                    />
                </div>

                <div class="mb-3">
                    <x-operation-intra-op-findings-input
                        name-prefix="field_intra_op_findings"
                        :saved-value="$intraOpFindings"
                        :legacy-value="$legacyIntraOpFindings"
                        :clinical-select-option-url="$clinicalSelectOptionUrl"
                        :required="$fieldRequired('intra_op_findings')"
                    />
                </div>

                <div>
                    <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.operation_notes') }}</label>
                    <textarea name="field_operation_notes" class="form-control follow-up-notes-textarea" rows="4">{{ $operationNotes }}</textarea>
                </div>
            </div>
        </div>
    </div>

    @if($showQuickFill)
        <div data-operation-tab-panel="quick_fill">
            <div class="card border-0 mb-3 clinical-phase-panel" style="--clinical-phase-bg: {{ $phaseStyle['background'] ?? '#B6D7A8' }}; --clinical-phase-color: {{ $phaseStyle['color'] ?? '#6AA84F' }};">
                <div class="clinical-phase-panel__body">
                    @include('pages.patients.partials.operation-quick-fill', [
                        'companies' => $quickFillCompanies,
                        'quickFillUrl' => $quickFillUrl,
                    ])
                </div>
            </div>
        </div>
    @endif
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('stageFields');
    window.initOperationStageFields?.(root);
    window.initOperationInsertionDepth?.(root);
    window.initOperationQuickFill?.(root);
});
</script>
@endpush
@endonce
