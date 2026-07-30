@extends('layouts.admin')

@section('title', __('patients.export.create_title'))

@section('content')
<x-page-header
    :title="__('patients.export.create_title')"
    :subtitle="__('patients.export.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => __('patients.export.create_title')],
    ]"
/>

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('patients.export.sections.options')">
            <form method="POST" action="{{ route('patients.export.store') }}" id="patientExportForm">
                @csrf

                @if($selectedCampaign)
                    <input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}">
                    <div class="mb-3">
                        <label class="form-group-admin__label">{{ __('patients.export.fields.campaign') }}</label>
                        <div class="fw-medium">{{ $selectedCampaign->name }}</div>
                        @if($selectedCampaign->code)
                            <div class="text-muted" style="font-size: 0.8125rem;">
                                {{ __('campaigns.fields.code') }}: <code>{{ $selectedCampaign->code }}</code>
                            </div>
                        @endif
                    </div>
                @else
                    <x-form-input :label="__('patients.export.fields.campaign')" name="campaign_id" type="select" required>
                        <option value="">{{ __('patients.placeholders.select_campaign') }}</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" @selected(old('campaign_id') == $campaign->id)>
                                {{ $campaign->name }} @if($campaign->code)({{ $campaign->code }})@endif
                            </option>
                        @endforeach
                    </x-form-input>
                @endif

                <div class="alert alert-light border mb-3" style="font-size: 0.875rem;">
                    <i class="ti ti-info-circle me-1"></i>
                    {{ __('patients.export.hints.basic_sheet') }}
                </div>

                <div class="mb-3">
                    <label class="form-group-admin__label d-block mb-2">{{ __('patients.export.sections.medical_records') }}</label>
                    <p class="text-muted mb-3" style="font-size: 0.8125rem;">{{ __('patients.export.hints.multiple_records') }}</p>

                    <div class="form-check mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="include_all_stages"
                            value="1"
                            id="includeAllStages"
                            @checked(old('include_all_stages'))
                        >
                        <label class="form-check-label fw-semibold" for="includeAllStages">
                            {{ __('patients.export.fields.all_stages') }}
                        </label>
                    </div>

                    <div class="border rounded p-3 bg-light-subtle">
                        @foreach($exportableStages as $stage)
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input stage-export-checkbox"
                                    type="checkbox"
                                    name="stages[]"
                                    value="{{ $stage->code }}"
                                    id="stage-{{ $stage->code }}"
                                    @checked(is_array(old('stages')) && in_array($stage->code, old('stages'), true))
                                >
                                <label class="form-check-label" for="stage-{{ $stage->code }}">
                                    {{ $stage->displayName() }}
                                    <span class="text-muted">(<code>{{ $stage->code }}</code>)</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-download me-1"></i> {{ __('patients.export.actions.download') }}
                    </button>
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('patients.export.sections.instructions')">
            <ul class="mb-0 ps-3" style="font-size: 0.875rem;">
                @foreach(__('patients.export.instructions') as $instruction)
                    <li class="mb-2 text-muted">{{ $instruction }}</li>
                @endforeach
            </ul>
        </x-card>

        <x-card :title="__('patients.export.sections.structure')" class="mt-3">
            <ul class="mb-0 ps-3" style="font-size: 0.875rem;">
                @foreach(__('patients.export.structure') as $item)
                    <li class="mb-2 text-muted">{{ $item }}</li>
                @endforeach
            </ul>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const includeAll = document.getElementById('includeAllStages');
    const stageChecks = document.querySelectorAll('.stage-export-checkbox');

    function syncStageChecks() {
        const disable = includeAll?.checked ?? false;
        stageChecks.forEach(function (checkbox) {
            checkbox.disabled = disable;
            if (disable) {
                checkbox.checked = false;
            }
        });
    }

    includeAll?.addEventListener('change', syncStageChecks);
    syncStageChecks();
})();
</script>
@endpush
