@extends('layouts.admin')

@section('title', __('workflow.stage_history') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="$patient->patient_name"
    :subtitle="__('workflow.stage_history')"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.stage_history')],
    ]"
/>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
            <i class="ti ti-history me-2 text-primary"></i>
            {{ __('workflow.stage_history') }}
        </h6>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> {{ __('patients.show.back') }}
        </a>
    </div>
    <div class="card-body p-0">
        @if($history->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ti ti-history fs-1 d-block mb-2"></i>
                {{ __('workflow.no_history') }}
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('workflow.history.date') }}</th>
                            <th>{{ __('workflow.history.from_stage') }}</th>
                            <th>{{ __('workflow.history.to_stage') }}</th>
                            <th>{{ __('workflow.history.changed_by') }}</th>
                            <th>{{ __('workflow.history.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $entry)
                        <tr>
                            <td class="text-nowrap small">
                                {{ $entry->changed_at?->format('d M Y, H:i') }}
                            </td>
                            <td>
                                @if($entry->fromStage)
                                    <span class="badge rounded-pill px-2 py-1"
                                          style="background-color: {{ $entry->fromStage->color ?? '#6B7280' }}; color: #fff;">
                                        {{ $entry->fromStage->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2 py-1"
                                      style="background-color: {{ $entry->toStage->color ?? '#3B82F6' }}; color: #fff;">
                                    {{ $entry->toStage->name }}
                                </span>
                            </td>
                            <td class="small">{{ $entry->changedBy?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $entry->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
