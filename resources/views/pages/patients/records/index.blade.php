@extends('layouts.admin')

@section('title', __('workflow.medical_records') . ' — ' . $patient->patient_name)

@section('content')
<x-page-header
    :title="$patient->patient_name"
    :subtitle="__('workflow.medical_records')"
    :breadcrumbs="[
        ['label' => __('menu.patients'), 'url' => route('patients.index')],
        ['label' => $patient->patient_name, 'url' => route('patients.show', $patient)],
        ['label' => __('workflow.medical_records')],
    ]"
>
    @can('medical_record.create')
    <a href="{{ route('patients.records.create', $patient) }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> {{ __('workflow.records.add') }}
    </a>
    @endcan
</x-page-header>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($records->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="ti ti-file-medical fs-1 d-block mb-2"></i>
                {{ __('workflow.no_records') }}
            </div>
        @else
            <div class="admin-table-scroll">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('workflow.records.date') }}</th>
                            <th>{{ __('workflow.records.stage') }}</th>
                            <th>{{ __('workflow.records.submitted_by') }}</th>
                            <th>{{ __('common.notes') }}</th>
                            <th class="text-end">{{ __('workflow.records.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration }}</td>
                            <td class="text-nowrap small">{{ $record->record_date?->format('d M Y') }}</td>
                            <td>
                                @if($record->stage)
                                    <span class="badge rounded-pill px-2 py-1"
                                          style="background-color: {{ $record->stage->color ?? '#3B82F6' }}; color:#fff;">
                                        {{ $record->stage->name }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small">{{ $record->submitter?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ Str::limit($record->notes, 60) }}</td>
                            <td class="text-end table-actions">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-table-dropdown
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        aria-label="{{ __('workflow.records.actions') }}"
                                    >
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                        @can('view', $record)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('patients.records.show', [$patient, $record]) }}">
                                                <i class="ti ti-eye me-2"></i> {{ __('workflow.records.view') }}
                                            </a>
                                        </li>
                                        @endcan
                                        @can('update', $record)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('patients.records.edit', [$patient, $record]) }}">
                                                <i class="ti ti-edit me-2"></i> {{ __('workflow.records.edit') }}
                                            </a>
                                        </li>
                                        @endcan
                                        @can('delete', $record)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('patients.records.destroy', [$patient, $record]) }}"
                                                  method="POST" class="delete-record-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger btn-delete-record">
                                                    <i class="ti ti-trash me-2"></i> {{ __('workflow.records.delete') }}
                                                </button>
                                            </form>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-record').forEach(function(btn) {
    btn.addEventListener('click', function() {
        Swal.fire({
            title: '{{ __('common.are_you_sure') }}',
            text: '{{ __('workflow.messages.confirm_delete') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: '{{ __('common.cancel') }}',
            confirmButtonText: '{{ __('workflow.records.delete') }}',
        }).then(function(result) {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    });
});
</script>
@endpush
