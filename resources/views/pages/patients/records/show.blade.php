@extends('layouts.admin')

@section('title', __('workflow.medical_records') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="__('workflow.medical_records')"
    :subtitle="$patient->patient_name"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.medical_records'), 'url' => route('patients.records.index', $patient)],
        ['label' => $record->record_date?->format('d M Y')],
    ]"
>
    <div class="d-flex gap-2">
        @can('medical_record.update')
        <a href="{{ route('patients.records.edit', [$patient, $record]) }}" class="btn btn-warning">
            <i class="ti ti-edit me-1"></i> {{ __('workflow.records.edit') }}
        </a>
        @endcan
        <a href="{{ route('patients.records.index', $patient) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> {{ __('patients.show.back') }}
        </a>
    </div>
</x-page-header>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted mb-3">{{ __('common.details') }}</h6>
                <dl class="row small mb-0">
                    <dt class="col-sm-6">{{ __('workflow.records.date') }}</dt>
                    <dd class="col-sm-6">{{ $record->record_date?->format('d M Y') }}</dd>

                    <dt class="col-sm-6">{{ __('workflow.records.stage') }}</dt>
                    <dd class="col-sm-6">
                        @if($record->stage)
                            <span class="badge rounded-pill"
                                  style="background-color: {{ $record->stage->color ?? '#3B82F6' }}; color:#fff;">
                                {{ $record->stage->name }}
                            </span>
                        @else
                            —
                        @endif
                    </dd>

                    <dt class="col-sm-6">{{ __('workflow.records.submitted_by') }}</dt>
                    <dd class="col-sm-6">{{ $record->submitter?->name ?? '—' }}</dd>

                    <dt class="col-sm-6">{{ __('common.created_at') }}</dt>
                    <dd class="col-sm-6">{{ $record->created_at?->format('d M Y, H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if(!empty($stageFields) && !empty($record->fields_json))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="ti ti-clipboard-list me-2 text-primary"></i>
                    {{ ucfirst(str_replace('_', ' ', $record->stage?->code ?? '')) }}
                </h6>
            </div>
            <div class="card-body">
                <dl class="row small mb-0">
                    @foreach($stageFields as $fieldKey => $fieldDef)
                    @php $val = $record->field($fieldKey); @endphp
                    @if(\App\Support\ClinicalCompositeFields::hasContent($fieldKey, $val, $fieldDef))
                    <dt class="col-sm-4 fw-semibold">{{ $fieldDef['label'] }}</dt>
                    <dd class="col-sm-8">
                        <x-clinical-value
                            :value="$val"
                            :type="$fieldDef['type'] ?? 'text'"
                            :field-key="$fieldKey"
                            :field-definition="$fieldDef"
                            :link-label="$fieldDef['label'] ?? null"
                        />
                    </dd>
                    @endif
                    @endforeach
                </dl>
            </div>
        </div>
        @endif

        @if($record->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted mb-2">{{ __('common.notes') }}</h6>
                <p class="mb-0">{{ $record->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
