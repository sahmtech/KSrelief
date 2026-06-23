{{-- Reusable medical record form partial --}}

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('workflow.records.stage') }} <span class="text-danger">*</span></label>
        <select name="stage_id" id="stageSelect" class="form-select @error('stage_id') is-invalid @enderror" required
                data-stage-fields-url="{{ route('patients.records.stage-fields', $patient) }}">
            <option value="">— {{ __('common.select') }} —</option>
            @foreach($stages as $stage)
                <option value="{{ $stage->id }}"
                        data-code="{{ $stage->code }}"
                        {{ old('stage_id', $selectedStageId ?? $record->stage_id ?? $patient->current_stage_id) == $stage->id ? 'selected' : '' }}>
                    {{ $stage->name }}
                </option>
            @endforeach
        </select>
        @error('stage_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('workflow.records.date') }} <span class="text-danger">*</span></label>
        <input type="date" name="record_date" class="form-control @error('record_date') is-invalid @enderror"
               value="{{ old('record_date', isset($record) ? $record->record_date?->format('Y-m-d') : date('Y-m-d')) }}"
               required>
        @error('record_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div id="stageFields">
    @include('pages.patients.records._stage_fields', [
        'stageFields' => $stageFields,
        'stageCode'   => $stageCode,
        'record'      => $record ?? null,
        'teamMembers' => $teamMembers ?? [],
        'patient'     => $patient,
        'implantCompanies' => $implantCompanies ?? collect(),
        'insertionApproaches' => $insertionApproaches ?? collect(),
        'implantElectrodeTypes' => $implantElectrodeTypes ?? collect(),
        'electrodeTypesUrl' => $electrodeTypesUrl ?? null,
    ])
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">{{ __('common.notes') }}</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $record->notes ?? '') }}</textarea>
</div>

@push('scripts')
<script>
(function () {
    document.getElementById('stageSelect')?.addEventListener('change', function () {
        const stageId = this.value;
        const url = this.dataset.stageFieldsUrl;
        const container = document.getElementById('stageFields');
        if (!stageId || !url || !container) return;

        fetch(url + '?stage_id=' + stageId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) throw new Error('stage_fields_failed');
            return r.json();
        })
        .then(data => {
            if (typeof data.html !== 'string') throw new Error('stage_fields_invalid');
            container.innerHTML = data.html;
            window.initOperationStageFields?.(container);
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger mb-0">{{ __('workflow.messages.stage_fields_load_failed') }}</div>';
        });
    });

    window.initOperationStageFields = function (root) {
    if (!root) return;

    initOperationMemberFields(root);

    const companySelect = root.querySelector('#implantCompanySelect');
    const electrodeSelect = root.querySelector('#electrodeTypeSelect');
    if (!companySelect || !electrodeSelect) return;

    const url = companySelect.dataset.electrodeUrl;

    function populateElectrodes(items) {
        const selectedId = electrodeSelect.value;
        electrodeSelect.innerHTML = '<option value="">— {{ __('common.select') }} —</option>';
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            if (String(selectedId) === String(item.id)) {
                option.selected = true;
            }
            electrodeSelect.appendChild(option);
        });
    }

    companySelect.onchange = function () {
        const companyId = this.value;
        const color = this.selectedOptions[0]?.dataset.color;
        this.style.color = color || '';
        this.style.fontWeight = companyId ? '600' : '';

        if (!companyId || !url) {
            populateElectrodes([]);
            return;
        }

        fetch(url + '?implant_company_id=' + encodeURIComponent(companyId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => populateElectrodes(data.data || []))
        .catch(() => populateElectrodes([]));
    };

    const initialOption = companySelect.selectedOptions[0];
    if (initialOption?.dataset.color) {
        companySelect.style.color = initialOption.dataset.color;
        companySelect.style.fontWeight = '600';
    }
};

    function initOperationMemberFields(root) {
        const surgeonSelect = root.querySelector('#field_surgeon');
        const specialistSelect = root.querySelector('#field_specialist');
        if (!surgeonSelect || !specialistSelect) return;

        let specialistChosenByUser = specialistSelect.value !== '';

        specialistSelect.addEventListener('change', function () {
            specialistChosenByUser = this.value !== '';
        });

        surgeonSelect.addEventListener('change', function () {
            if (!specialistChosenByUser
                && specialistSelect.value
                && specialistSelect.value === surgeonSelect.value) {
                specialistSelect.value = '';
            }
        });
    }

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('stageFields');
    window.initOperationStageFields?.(container);
});
})();
</script>
@endpush
