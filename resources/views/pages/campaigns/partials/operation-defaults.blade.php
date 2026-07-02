@php
    $operationDefaultCompanies = $operationDefaultCompanies ?? collect();
    $operationDefaultsByCompany = $operationDefaultsByCompany ?? [];
    $operationElectrodesByCompany = $operationElectrodesByCompany ?? [];
    $insertionApproaches = $insertionApproaches ?? collect();
@endphp

@if($operationDefaultCompanies->isNotEmpty())
@include('pages.patients.partials.clinical-fallback-styles')
<div class="col-12 campaign-operation-defaults" id="operation-record-defaults">
    <x-card :title="__('campaigns.sections.operation_defaults')">
        <p class="text-muted small mb-3">{{ __('campaigns.hints.operation_defaults') }}</p>

        <ul class="nav nav-tabs operation-form-tabs mb-3" role="tablist">
            @foreach($operationDefaultCompanies as $company)
                <li class="nav-item" role="presentation">
                    <button type="button"
                            class="nav-link @if($loop->first) active @endif"
                            role="tab"
                            data-bs-toggle="tab"
                            data-bs-target="#campaignOpDefaults{{ $company->id }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        <span style="color: {{ $company->color }}; font-weight: 600;">{{ $company->name }}</span>
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($operationDefaultCompanies as $company)
                @php
                    $companyId = (int) $company->id;
                    $prefix = "operation_defaults[{$companyId}]";
                    $defaults = $operationDefaultsByCompany[$companyId] ?? [];
                    $electrodes = $operationElectrodesByCompany[$companyId] ?? collect();
                    $electrodeTypeId = old("operation_defaults.{$companyId}.electrode_type_id", $defaults['electrode_type_id'] ?? null);
                    $insertionApproachId = old("operation_defaults.{$companyId}.insertion_approach_id", $defaults['insertion_approach_id'] ?? null);
                    $insertionDepth = old("operation_defaults.{$companyId}.insertion_depth", $defaults['insertion_depth'] ?? null);
                    $audioTest = old("operation_defaults.{$companyId}.audio_test", $defaults['audio_test'] ?? null);
                    $intraOpFindings = old("operation_defaults.{$companyId}.intra_op_findings", $defaults['intra_op_findings'] ?? null);
                @endphp
                <div class="tab-pane fade @if($loop->first) show active @endif"
                     id="campaignOpDefaults{{ $company->id }}"
                     role="tabpanel">
                    <input type="hidden" name="{{ $prefix }}[implant_company_id]" value="{{ $company->id }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.electrode_type') }}</label>
                            <select name="{{ $prefix }}[electrode_type_id]" class="form-select">
                                <option value="">— {{ __('common.select') }} —</option>
                                @foreach($electrodes as $electrode)
                                    <option value="{{ $electrode->id }}" @selected((string) $electrodeTypeId === (string) $electrode->id)>
                                        {{ $electrode->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">{{ __('workflow.fields.insertion_approach') }}</label>
                            <select name="{{ $prefix }}[insertion_approach_id]" class="form-select">
                                <option value="">— {{ __('common.select') }} —</option>
                                @foreach($insertionApproaches as $approach)
                                    <option value="{{ $approach->id }}" @selected((string) $insertionApproachId === (string) $approach->id)>
                                        {{ $approach->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pre-op-section-divider pt-3 mt-2 mb-3">
                        <x-operation-insertion-depth-input
                            :name-prefix="$prefix.'[insertion_depth]'"
                            :saved-value="$insertionDepth"
                        />
                    </div>

                    <div class="pre-op-section-divider pt-3 mt-2 mb-3">
                        <x-operation-audio-test-input
                            :name-prefix="$prefix.'[audio_test]'"
                            :saved-value="$audioTest"
                        />
                    </div>

                    <div>
                        <x-operation-intra-op-findings-input
                            :name-prefix="$prefix.'[intra_op_findings]'"
                            :saved-value="$intraOpFindings"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
@endif
