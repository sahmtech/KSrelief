{{-- Medical Records tab: clinical dossier (Excel-like) + records list --}}
{{-- Requires: $patient, $medicalRecords, $clinicalProfile (optional) --}}

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
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

@if(!empty($clinicalProfile))
<ul class="nav nav-pills mb-3" id="recordsSubTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="records-dossier-tab" data-bs-toggle="pill" data-bs-target="#records-dossier-pane" type="button" role="tab">
            <i class="ti ti-layout-grid me-1"></i> {{ __('workflow.records.view_dossier') }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="records-list-tab" data-bs-toggle="pill" data-bs-target="#records-list-pane" type="button" role="tab">
            <i class="ti ti-list me-1"></i> {{ __('workflow.records.view_list') }}
        </button>
    </li>
</ul>

<div class="tab-content" id="recordsSubTabsContent">
    <div class="tab-pane fade show active" id="records-dossier-pane" role="tabpanel">
        @include('pages.patients.partials.clinical-dossier', [
            'patient' => $patient,
            'clinicalProfile' => $clinicalProfile,
        ])
    </div>
    <div class="tab-pane fade" id="records-list-pane" role="tabpanel">
        @include('pages.patients.partials.records-list')
    </div>
</div>
@else
    @include('pages.patients.partials.records-list')
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
