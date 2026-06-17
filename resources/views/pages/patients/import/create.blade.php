@extends('layouts.admin')

@section('title', __('patients.import.create_title'))

@section('content')
<x-page-header
    :title="__('patients.import.create_title')"
    :subtitle="__('patients.import.create_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => __('patients.import.title'), 'url' => route('patients.import.index')],
        ['label' => __('patients.import.create_title')],
    ]"
>
    <x-slot:actions>
        <a href="{{ route('patients.import.template', $selectedCampaign ? ['campaign_id' => $selectedCampaign->id] : []) }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-download me-1"></i> {{ __('patients.import.download_template') }}
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-5">
        <x-card :title="__('patients.import.sections.upload')">
            <p class="text-muted mb-3" style="font-size: 0.875rem;">{{ __('patients.import.subtitle') }}</p>

            <form method="POST" action="{{ route('patients.import.store') }}" enctype="multipart/form-data">
                @csrf

                @if($selectedCampaign)
                    <input type="hidden" name="campaign_id" value="{{ $selectedCampaign->id }}">
                    <div class="mb-3">
                        <label class="form-group-admin__label">{{ __('patients.import.fields.campaign') }}</label>
                        <div class="fw-medium">{{ $selectedCampaign->name }}</div>
                        @if($selectedCampaign->code)
                            <div class="text-muted" style="font-size: 0.8125rem;">
                                {{ __('campaigns.fields.code') }}: <code>{{ $selectedCampaign->code }}</code>
                            </div>
                        @endif
                    </div>
                @else
                    <x-form-input :label="__('patients.import.fields.campaign')" name="campaign_id" type="select">
                        <option value="">{{ __('patients.filters.all_campaigns') }}</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" @selected(old('campaign_id') == $campaign->id)>
                                {{ $campaign->name }} @if($campaign->code)({{ $campaign->code }})@endif
                            </option>
                        @endforeach
                    </x-form-input>
                @endif

                <div class="mb-3">
                    <label class="form-group-admin__label" for="import_file">{{ __('patients.import.fields.file') }}</label>
                    <input
                        type="file"
                        name="file"
                        id="import_file"
                        class="form-group-admin__input @error('file') is-invalid @enderror"
                        accept=".xlsx,.xls,.csv"
                        required
                    >
                    @error('file')
                        <div class="form-group-admin__error">{{ $message }}</div>
                    @enderror
                </div>

                <x-form-input :label="__('patients.import.fields.notes')" name="notes" type="textarea" :value="old('notes')" />

                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i> {{ __('patients.import.actions.upload') }}
                    </button>
                    <a href="{{ route('patients.import.index') }}" class="btn btn-outline-secondary">{{ __('common.cancel') }}</a>
                </div>
            </form>
        </x-card>

        <x-card :title="__('patients.import.sections.instructions')" class="mt-3">
            <ul class="mb-0 ps-3" style="font-size: 0.875rem;">
                @foreach(__('patients.import.instructions') as $instruction)
                    <li class="mb-2 text-muted">{{ $instruction }}</li>
                @endforeach
            </ul>
        </x-card>
    </div>

    <div class="col-lg-7">
        <x-card :title="__('patients.import.sections.reference')" :flush="true">
            <ul class="nav nav-tabs border-0 px-3 pt-2" id="refTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="eligibility-ref-tab" data-bs-toggle="tab" data-bs-target="#eligibility-ref" type="button" role="tab">
                        {{ __('patients.fields.eligibility_status') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="stages-ref-tab" data-bs-toggle="tab" data-bs-target="#stages-ref" type="button" role="tab">
                        {{ __('patients.fields.current_stage') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gender-ref-tab" data-bs-toggle="tab" data-bs-target="#gender-ref" type="button" role="tab">
                        {{ __('patients.fields.gender') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="campaigns-ref-tab" data-bs-toggle="tab" data-bs-target="#campaigns-ref" type="button" role="tab">
                        {{ __('patients.import.fields.campaign') }}
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="refTabsContent">
                <div class="tab-pane fade show active" id="eligibility-ref" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>{{ __('patients.fields.eligibility_status') }}</th><th>Code</th></tr></thead>
                            <tbody>
                                @foreach($eligibilityStatuses as $status)
                                    <tr>
                                        <td>
                                            <span class="badge border" style="background-color: {{ $status->color }}20; color: {{ $status->color }};">{{ $status->name }}</span>
                                        </td>
                                        <td><code>{{ $status->code }}</code></td>
                                    </tr>
                                @endforeach
                                <tr class="table-secondary">
                                    <td colspan="2">
                                        <code>admission_status</code>:
                                        <code class="ms-2">admitted</code> /
                                        <code class="ms-1">not_admitted</code>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="stages-ref" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>{{ __('patients.fields.current_stage') }}</th><th>Code</th></tr></thead>
                            <tbody>
                                @foreach($patientStages as $stage)
                                    <tr>
                                        <td>{{ $stage->name }} @if($stage->is_default)<span class="badge bg-primary-subtle text-primary ms-1">default</span>@endif</td>
                                        <td><code>{{ $stage->code }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="gender-ref" role="tabpanel">
                    <div class="p-3">
                        @foreach(\App\Enums\Gender::cases() as $gender)
                            <div class="mb-2">
                                {{ $gender->label() }} → <code>{{ $gender->value }}</code>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="campaigns-ref" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>{{ __('campaigns.fields.name') }}</th><th>Code</th></tr></thead>
                            <tbody>
                                @foreach($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->name }}</td>
                                        <td><code>{{ $campaign->code ?? '—' }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
