@extends('layouts.admin')

@section('title', __('workflow.records.edit') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="__('workflow.records.edit')"
    :subtitle="$patient->patient_name"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.medical_records'), 'url' => route('patients.records.index', $patient)],
        ['label' => __('workflow.records.edit')],
    ]"
/>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-file-pencil me-2 text-primary"></i>
            {{ __('workflow.records.edit') }}
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('patients.records.update', [$patient, $record]) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            @include('pages.patients.records._form', [
                'patient'         => $patient,
                'stages'          => $stages,
                'stageFields'     => $stageFields,
                'stageCode'       => $stageCode,
                'record'          => $record,
                'teamMembers'     => $teamMembers,
                'selectedStageId' => $selectedStageId ?? null,
            ])

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('common.save') }}
                </button>
                @if(($stageCode ?? '') === 'operation')
                    <button type="submit" name="export_pdf" value="1" class="btn btn-success">
                        <i class="ti ti-file-type-pdf me-1"></i>{{ __('workflow.operation.save_and_export_pdf') }}
                    </button>
                @endif
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-light">
                    {{ __('common.cancel') }}
                </a>
            </div>
            @if(($stageCode ?? '') === 'operation')
                <p class="form-text mt-2 mb-0">{{ __('workflow.operation.export_pdf_hint') }}</p>
            @endif
        </form>
    </div>
</div>
@endsection
