@extends('layouts.admin')

@section('title', __('patients.import.title'))

@section('content')
<x-page-header
    :title="__('patients.import.title')"
    :subtitle="__('patients.import.subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.title'), 'url' => route('patients.index')],
        ['label' => __('patients.import.title')],
    ]"
>
    <x-slot:actions>
        @can('importExcel', \App\Models\Patient::class)
            <a href="{{ route('patients.import.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-upload me-1"></i> {{ __('patients.import.create_title') }}
            </a>
        @endcan
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.total')" :value="$stats['total']" icon="ti ti-file-spreadsheet" variant="primary" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.pending_review')" :value="$stats['pending_review']" icon="ti ti-eye" variant="warning" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.processing')" :value="$stats['processing']" icon="ti ti-loader" variant="secondary" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.completed')" :value="$stats['completed']" icon="ti ti-circle-check" variant="success" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.failed')" :value="$stats['failed']" icon="ti ti-circle-x" variant="danger" />
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <x-stats-card :label="__('patients.import.stats.patients_imported')" :value="$stats['patients_imported']" icon="ti ti-users" variant="success" />
    </div>
</div>

<x-card :flush="true">
    <x-datatable
        id="importBatchesTable"
        :options="[
            'order' => [[0, 'desc']],
            'columnDefs' => [
                ['targets' => 8, 'orderable' => false, 'width' => '60px', 'className' => 'text-end'],
            ],
        ]"
    >
        <x-slot:head>
            <tr>
                <th>{{ __('patients.import.table.date') }}</th>
                <th>{{ __('patients.import.table.campaign') }}</th>
                <th>{{ __('patients.import.table.uploaded_by') }}</th>
                <th>{{ __('patients.import.table.rows') }}</th>
                <th>{{ __('patients.import.table.valid') }}</th>
                <th>{{ __('patients.import.table.invalid') }}</th>
                <th>{{ __('patients.import.table.duplicates') }}</th>
                <th>{{ __('patients.import.table.status') }}</th>
                <th class="text-end">{{ __('patients.import.table.actions') }}</th>
            </tr>
        </x-slot:head>
        @foreach($batches as $batch)
            <tr>
                <td>
                    <div class="fw-medium">{{ $batch->created_at->format('Y-m-d') }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $batch->created_at->format('H:i') }}</div>
                </td>
                <td>{{ $batch->campaign?->name ?? '—' }}</td>
                <td>{{ $batch->importer?->name ?? '—' }}</td>
                <td>{{ $batch->total_rows }}</td>
                <td><span class="text-success fw-medium">{{ $batch->valid_rows }}</span></td>
                <td><span class="{{ $batch->invalid_rows > 0 ? 'text-danger' : 'text-muted' }} fw-medium">{{ $batch->invalid_rows }}</span></td>
                <td><span class="{{ $batch->duplicate_rows > 0 ? 'text-warning' : 'text-muted' }} fw-medium">{{ $batch->duplicate_rows }}</span></td>
                <td>
                    <span class="badge-status {{ $batch->statusBadgeClass() }}">{{ $batch->statusLabel() }}</span>
                </td>
                <td class="text-end table-actions">
                    <div class="dropdown">
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            data-table-dropdown
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item" href="{{ route('patients.import.show', $batch) }}">
                                    <i class="ti ti-eye me-2"></i>{{ __('patients.import.actions.view') }}
                                </a>
                            </li>
                            @if($batch->status?->isApprovable() && auth()->user()?->can('patient.import_approve'))
                                <li>
                                    <form method="POST" action="{{ route('patients.import.approve', $batch) }}">
                                        @csrf
                                        <button type="submit"
                                            class="dropdown-item text-success"
                                            data-confirm="{{ __('patients.import.messages.confirm_approve', ['count' => $batch->valid_rows]) }}">
                                            <i class="ti ti-check me-2"></i>{{ __('patients.import.actions.approve') }}
                                        </button>
                                    </form>
                                </li>
                            @endif
                            @if(in_array($batch->status?->value, ['review','completed','failed']))
                                <li>
                                    <a class="dropdown-item" href="{{ route('patients.import.errors', $batch) }}">
                                        <i class="ti ti-download me-2"></i>{{ __('patients.import.actions.download_errors') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-datatable>
</x-card>
@endsection
