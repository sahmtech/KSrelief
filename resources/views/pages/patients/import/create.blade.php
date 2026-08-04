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
        <a href="{{ route('patients.import.template') }}" class="btn btn-outline-primary btn-sm">
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
                    <x-form-input :label="__('patients.import.fields.campaign')" name="campaign_id" type="select" required>
                        <option value="">{{ __('patients.placeholders.select_campaign') }}</option>
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
                    <div class="form-text">{{ __('patients.import.file_hint') }}</div>
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
        <x-card :title="__('patients.import.sections.template_columns')" :flush="true">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('patients.import.template_table.column') }}</th>
                            <th>{{ __('patients.import.template_table.label') }}</th>
                            <th>{{ __('patients.import.template_table.required') }}</th>
                            <th>{{ __('patients.import.template_table.rules') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templateColumns as $column)
                            <tr>
                                <td><code>{{ $column }}</code></td>
                                <td>{{ __('patients.import.fields.'.$column) }}</td>
                                <td>
                                    @if(in_array($column, $requiredColumns, true))
                                        <span class="badge bg-danger-subtle text-danger">{{ __('patients.import.column_required') }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ __('patients.import.column_optional') }}</span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.8125rem;">
                                    {{ __('patients.import.column_hints.'.$column) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card :title="__('patients.import.sections.gender_values')" class="mt-3">
            <div class="d-flex flex-wrap gap-3" style="font-size: 0.875rem;">
                @foreach(\App\Enums\Gender::cases() as $gender)
                    <div>
                        <span class="text-muted">{{ $gender->label() }}</span>
                        <code class="ms-1">{{ $gender->value }}</code>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card :title="__('patients.import.sections.defaults')" class="mt-3">
            <ul class="mb-0 ps-3 text-muted" style="font-size: 0.875rem;">
                @foreach(__('patients.import.default_notes') as $note)
                    <li class="mb-2">{{ $note }}</li>
                @endforeach
            </ul>
        </x-card>
    </div>
</div>
@endsection
