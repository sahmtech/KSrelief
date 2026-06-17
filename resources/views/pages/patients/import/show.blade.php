@extends('layouts.admin')

@section('title', __('patients.import.show_title'))

@section('content')
<x-page-header
    :title="__('patients.import.show_title')"
    :subtitle="__('patients.import.show_subtitle')"
    :breadcrumbs="[
        ['label' => __('menu.patients')],
        ['label' => __('patients.import.title'), 'url' => route('patients.import.index')],
        ['label' => '#'.$batch->id],
    ]"
>
    <x-slot:actions>
        @if(in_array($batch->status?->value, ['review','completed','failed']))
            <a href="{{ route('patients.import.errors', $batch) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-download me-1"></i> {{ __('patients.import.actions.download_errors') }}
            </a>
        @endif
        @if($canApprove)
            <form method="POST" action="{{ route('patients.import.approve', $batch) }}" class="d-inline">
                @csrf
                <button type="submit"
                    class="btn btn-success btn-sm"
                    data-confirm="{{ __('patients.import.messages.confirm_approve', ['count' => $batch->valid_rows]) }}">
                    <i class="ti ti-check me-1"></i> {{ __('patients.import.actions.approve') }}
                </button>
            </form>
        @endif
    </x-slot:actions>
</x-page-header>

{{-- Batch Information --}}
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--primary"><i class="ti ti-file-spreadsheet"></i></div>
            <div>
                <div class="user-stat-tile__value">{{ $batch->total_rows }}</div>
                <div class="user-stat-tile__label">{{ __('patients.import.table.rows') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--success"><i class="ti ti-circle-check"></i></div>
            <div>
                <div class="user-stat-tile__value text-success">{{ $batch->valid_rows }}</div>
                <div class="user-stat-tile__label">{{ __('patients.import.table.valid') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--danger"><i class="ti ti-circle-x"></i></div>
            <div>
                <div class="user-stat-tile__value {{ $batch->invalid_rows > 0 ? 'text-danger' : '' }}">{{ $batch->invalid_rows }}</div>
                <div class="user-stat-tile__label">{{ __('patients.import.table.invalid') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="user-stat-tile">
            <div class="user-stat-tile__icon user-stat-tile__icon--warning"><i class="ti ti-copy"></i></div>
            <div>
                <div class="user-stat-tile__value {{ $batch->duplicate_rows > 0 ? 'text-warning' : '' }}">{{ $batch->duplicate_rows }}</div>
                <div class="user-stat-tile__label">{{ __('patients.import.table.duplicates') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Batch Details --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <x-card :title="__('patients.import.sections.statistics')">
            <div class="user-info-list">
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('patients.import.table.status') }}</div>
                    <div class="user-info-list__value">
                        <span class="badge-status {{ $batch->statusBadgeClass() }}">{{ $batch->statusLabel() }}</span>
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('patients.import.table.campaign') }}</div>
                    <div class="user-info-list__value">
                        @if($batch->campaign)
                            <a href="{{ route('campaigns.show', $batch->campaign) }}" class="text-decoration-none">{{ $batch->campaign->name }}</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('patients.import.table.uploaded_by') }}</div>
                    <div class="user-info-list__value">{{ $batch->importer?->name ?? '—' }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('patients.import.table.date') }}</div>
                    <div class="user-info-list__value">{{ $batch->created_at->format('Y-m-d H:i') }}</div>
                </div>
                <div class="user-info-list__item">
                    <div class="user-info-list__label">{{ __('patients.fields.file_number') }}</div>
                    <div class="user-info-list__value"><code>{{ $batch->original_file_name }}</code></div>
                </div>
                @if($batch->notes)
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('patients.import.fields.notes') }}</div>
                        <div class="user-info-list__value">{{ $batch->notes }}</div>
                    </div>
                @endif
                @if($batch->failure_reason)
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('patients.import.status.failed') }}</div>
                        <div class="user-info-list__value text-danger">{{ $batch->failure_reason }}</div>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    @if($batch->status?->value === 'completed')
        <div class="col-lg-6">
            <x-card :title="__('patients.import.sections.approval')">
                <div class="user-info-list">
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('patients.import.table.imported') }}</div>
                        <div class="user-info-list__value fw-bold text-success fs-5">{{ $batch->imported_count }}</div>
                    </div>
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('patients.fields.updated_by') }}</div>
                        <div class="user-info-list__value">{{ $batch->approver?->name ?? '—' }}</div>
                    </div>
                    <div class="user-info-list__item">
                        <div class="user-info-list__label">{{ __('patients.fields.updated_at') }}</div>
                        <div class="user-info-list__value">{{ $batch->approved_at?->format('Y-m-d H:i') ?? '—' }}</div>
                    </div>
                </div>
            </x-card>
        </div>
    @elseif($canApprove)
        <div class="col-lg-6">
            <x-card :title="__('patients.import.sections.approval')">
                <div class="alert alert-warning mb-3" style="font-size: 0.875rem;">
                    <i class="ti ti-alert-triangle me-2"></i>
                    {{ __('patients.import.messages.confirm_approve', ['count' => $batch->valid_rows]) }}
                </div>
                <form method="POST" action="{{ route('patients.import.approve', $batch) }}">
                    @csrf
                    <button type="submit"
                        class="btn btn-success"
                        data-confirm="{{ __('patients.import.messages.confirm_approve', ['count' => $batch->valid_rows]) }}">
                        <i class="ti ti-check me-1"></i> {{ __('patients.import.actions.approve') }}
                    </button>
                </form>
            </x-card>
        </div>
    @endif
</div>

{{-- Row Review Table --}}
@if($batch->status?->value !== 'uploaded' && $batch->status?->value !== 'processing')
    <x-card :title="__('patients.import.sections.review')" :flush="true">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>{{ __('patients.table.name') }}</th>
                        <th>{{ __('patients.table.file_number') }}</th>
                        <th>{{ __('patients.import.table.status') }}</th>
                        <th>{{ __('patients.import.table.valid') }}</th>
                        <th>Errors / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="{{ $log->is_duplicate ? 'table-warning' : (! $log->is_valid ? 'table-danger' : '') }}">
                            <td><code>{{ $log->row_number }}</code></td>
                            <td>{{ $log->patient_name ?? '—' }}</td>
                            <td><code>{{ $log->file_number ?? '—' }}</code></td>
                            <td>
                                <span class="badge-status {{ $log->rowStatusBadgeClass() }}">{{ $log->rowStatusLabel() }}</span>
                                @if($log->patient_id)
                                    <a href="{{ route('patients.show', $log->patient_id) }}" class="ms-1 badge bg-primary-subtle text-primary border-0" style="font-size: 0.7rem;">
                                        <i class="ti ti-external-link"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($log->is_valid && !$log->is_duplicate)
                                    <i class="ti ti-check text-success"></i>
                                @else
                                    <i class="ti ti-x text-danger"></i>
                                @endif
                            </td>
                            <td>
                                @if($log->validation_errors)
                                    <ul class="mb-0 ps-3" style="font-size: 0.8rem;">
                                        @foreach($log->validation_errors as $error)
                                            <li class="text-danger">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($log->duplicate_reason)
                                    <div class="text-warning" style="font-size: 0.8rem;">
                                        <i class="ti ti-copy me-1"></i>{{ $log->duplicate_reason }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-3">
                {{ $logs->links() }}
            </div>
        @endif
    </x-card>
@elseif($batch->status?->value === 'processing')
    <x-card>
        <div class="text-center text-muted py-4">
            <i class="ti ti-loader d-block mb-2" style="font-size: 2.5rem; opacity: 0.4;"></i>
            {{ __('patients.import.messages.uploaded') }}
        </div>
    </x-card>
@endif
@endsection

@push('scripts')
<script>
@if(in_array($batch->status?->value, ['uploaded', 'processing']))
setTimeout(() => window.location.reload(), 5000);
@endif
</script>
@endpush
