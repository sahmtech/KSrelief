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
    ])
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">{{ __('common.notes') }}</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $record->notes ?? '') }}</textarea>
</div>

@push('scripts')
<script>
document.getElementById('stageSelect')?.addEventListener('change', function () {
    const stageId = this.value;
    const url = this.dataset.stageFieldsUrl;
    if (!stageId || !url) return;

    fetch(url + '?stage_id=' + stageId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('stageFields').innerHTML = data.html;
    });
});
</script>
@endpush
