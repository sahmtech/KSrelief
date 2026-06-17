{{-- Medical Records tab on patient profile --}}
{{-- Requires: $patient, $medicalRecords (Collection) --}}

<div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="mb-0 fw-semibold">
        <i class="ti ti-file-medical me-2 text-primary"></i>
        {{ __('workflow.medical_records') }}
    </h6>
    @can('create', [\App\Models\MedicalRecord::class, $patient])
    <a href="{{ route('patients.records.create', $patient) }}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus me-1"></i> {{ __('workflow.records.add') }}
    </a>
    @endcan
</div>

@if($medicalRecords->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="ti ti-file-medical d-block mb-2" style="font-size: 2.5rem; opacity: .4;"></i>
        {{ __('workflow.no_records') }}
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
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
                @foreach($medicalRecords as $record)
                <tr>
                    <td class="small text-muted">{{ $loop->iteration }}</td>
                    <td class="small text-nowrap">{{ $record->record_date?->format('d M Y') }}</td>
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
                    <td class="small text-muted">{{ Str::limit($record->notes, 50) }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            @can('view', $record)
                            <a href="{{ route('patients.records.show', [$patient, $record]) }}"
                               class="btn btn-xs btn-light" title="{{ __('workflow.records.view') }}">
                                <i class="ti ti-eye"></i>
                            </a>
                            @endcan
                            @can('update', $record)
                            <a href="{{ route('patients.records.edit', [$patient, $record]) }}"
                               class="btn btn-xs btn-light" title="{{ __('workflow.records.edit') }}">
                                <i class="ti ti-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $record)
                            <form action="{{ route('patients.records.destroy', [$patient, $record]) }}"
                                  method="POST" class="delete-record-form d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-xs btn-light text-danger btn-delete-record"
                                        title="{{ __('workflow.records.delete') }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-record').forEach(function(btn) {
    btn.addEventListener('click', function() {
        Swal.fire({
            title: '{{ __('common.are_you_sure') }}',
            text: '{{ __('workflow.messages.confirm_delete') }}',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: '{{ __('common.cancel') }}',
            confirmButtonText: '{{ __('workflow.records.delete') }}',
        }).then(function(r) { if (r.isConfirmed) btn.closest('form').submit(); });
    });
});
</script>
@endpush
