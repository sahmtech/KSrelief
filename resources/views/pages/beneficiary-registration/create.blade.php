@extends('layouts.public')

@section('title', __('beneficiary_registration.title'))

@section('content')
@php
    $extraTypes = collect($meta['extra_types'] ?? []);
    $defaultExtraType = old('extra_type', $extraTypes->first()['id'] ?? 8);
    $genders = $meta['genders'] ?? [];
    $countries = $meta['countries'] ?? [];
    $marital = $meta['marital_status'] ?? [];
    $diseases = $meta['diseases'] ?? [];
    $diseaseTypes = $meta['disease_types'] ?? [];
    $treatmentTypes = $meta['treatment_types'] ?? [];
    $oldDiseases = old('health.diseases', [['disease' => '', 'type' => '', 'treatment_type' => '', 'treatment_cost' => '', 'notes' => '']]);
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <h1 class="h3 mb-2">{{ __('beneficiary_registration.public_heading') }}</h1>
        <p class="text-muted mb-0">{{ __('beneficiary_registration.public_intro') }}</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register.store') }}" enctype="multipart/form-data" id="beneficiaryRegistrationForm" novalidate>
    @csrf

    <div class="beneficiary-wizard-steps" id="wizardSteps"></div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            {{-- Step: basic --}}
            <div class="beneficiary-panel" data-step="basic">
                <h2 class="h5 mb-3">{{ __('beneficiary_registration.steps.basic') }}</h2>

                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.photo') }}</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.extra_type') }} <span class="text-danger">*</span></label>
                    <select name="extra_type" id="extraTypeSelect" class="form-select" required>
                        @foreach($extraTypes as $type)
                            <option
                                value="{{ $type['id'] }}"
                                data-steps="{{ implode(',', $type['visible_steps'] ?? []) }}"
                                @selected((string) $defaultExtraType === (string) $type['id'])
                            >{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">{{ __('beneficiary_registration.fields.extra_type_hint') }}</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('beneficiary_registration.fields.name_placeholder') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.national_number') }} <span class="text-danger">*</span></label>
                        <input type="text" name="national_number" class="form-control" value="{{ old('national_number') }}" maxlength="10" inputmode="numeric" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">{{ __('beneficiary_registration.fields.gender') }} <span class="text-danger">*</span></label>
                        @foreach($genders as $gender)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input gender-radio" type="radio" name="gender" id="gender{{ $gender['id'] }}" value="{{ $gender['id'] }}" @checked((string) old('gender', '1') === (string) $gender['id']) required>
                                <label class="form-check-label" for="gender{{ $gender['id'] }}">{{ $gender['label'] }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.mobile') }} <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" maxlength="10" inputmode="numeric" placeholder="05xxxxxxxx" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.country') }} <span class="text-danger">*</span></label>
                        <select name="country" class="form-select" required>
                            @foreach($countries as $country)
                                <option value="{{ $country['code'] }}" @selected(old('country', $meta['default_country'] ?? 'sa') === $country['code'])>{{ $country['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.birth_date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.marital_status') }} <span class="text-danger">*</span></label>
                        <select name="marital_status" id="maritalStatusSelect" class="form-select" required>
                            <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                            @foreach($marital as $option)
                                <option
                                    value="{{ $option['id'] }}"
                                    data-male="{{ $option['label_male'] }}"
                                    data-female="{{ $option['label_female'] }}"
                                    @selected((string) old('marital_status') === (string) $option['id'])
                                >{{ $option['label_male'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step: extra --}}
            <div class="beneficiary-panel" data-step="extra" hidden>
                <h2 class="h5 mb-3">{{ __('beneficiary_registration.steps.extra') }}</h2>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.case_description') }} <span class="text-danger">*</span></label>
                    <textarea name="extra[case_description]" class="form-control" rows="4">{{ old('extra.case_description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.medical_reports') }} <span class="text-danger">*</span></label>
                    <input type="file" name="extra[medical_reports][]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.email') }} <span class="text-danger">*</span></label>
                    <input type="email" name="extra[email]" class="form-control" value="{{ old('extra.email') }}">
                </div>
            </div>

            {{-- Step: health --}}
            <div class="beneficiary-panel" data-step="health" hidden>
                <h2 class="h5 mb-3">{{ __('beneficiary_registration.steps.health') }}</h2>
                <div class="mb-3">
                    <label class="form-label fw-semibold d-block">{{ __('beneficiary_registration.fields.health_status') }} <span class="text-danger">*</span></label>
                    @foreach($meta['health_status'] ?? [] as $status)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input health-status-radio" type="radio" name="health[health_status]" id="healthStatus{{ $status['id'] }}" value="{{ $status['id'] }}" @checked((string) old('health.health_status', '1') === (string) $status['id']) required>
                            <label class="form-check-label" for="healthStatus{{ $status['id'] }}">{{ $status['label'] }}</label>
                        </div>
                    @endforeach
                </div>

                <div id="sickHealthBlock" @if((string) old('health.health_status', '1') !== '2') hidden @endif>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" role="switch" name="health[handicapped]" id="handicappedToggle" value="1" @checked(old('health.handicapped'))>
                                <label class="form-check-label" for="handicappedToggle">{{ __('beneficiary_registration.fields.handicapped') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.handicapped_percentage') }}</label>
                            <div class="input-group">
                                <input type="number" name="health[handicapped_percentage]" class="form-control" min="0" max="100" value="{{ old('health.handicapped_percentage') }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">{{ __('beneficiary_registration.fields.diseases') }} <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addDiseaseBtn">
                            <i class="ti ti-plus"></i> {{ __('beneficiary_registration.add_disease') }}
                        </button>
                    </div>
                    <div id="diseasesContainer">
                        @foreach($oldDiseases as $index => $disease)
                            <div class="disease-row" data-disease-row>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label small">{{ __('beneficiary_registration.fields.disease') }}</label>
                                        <select name="health[diseases][{{ $index }}][disease]" class="form-select form-select-sm">
                                            <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                                            @foreach($diseases as $option)
                                                <option value="{{ $option['id'] }}" @selected((string) ($disease['disease'] ?? '') === (string) $option['id'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">{{ __('beneficiary_registration.fields.disease_type') }}</label>
                                        <select name="health[diseases][{{ $index }}][type]" class="form-select form-select-sm">
                                            <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                                            @foreach($diseaseTypes as $option)
                                                <option value="{{ $option['id'] }}" @selected((string) ($disease['type'] ?? '') === (string) $option['id'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">{{ __('beneficiary_registration.fields.treatment_type') }}</label>
                                        <select name="health[diseases][{{ $index }}][treatment_type]" class="form-select form-select-sm">
                                            <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                                            @foreach($treatmentTypes as $option)
                                                <option value="{{ $option['id'] }}" @selected((string) ($disease['treatment_type'] ?? '') === (string) $option['id'])>{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" data-remove-disease>{{ __('beneficiary_registration.remove') }}</button>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">{{ __('beneficiary_registration.fields.treatment_cost') }}</label>
                                        <input type="text" name="health[diseases][{{ $index }}][treatment_cost]" class="form-control form-control-sm" value="{{ $disease['treatment_cost'] ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">{{ __('beneficiary_registration.fields.disease_notes') }}</label>
                                        <input type="text" name="health[diseases][{{ $index }}][notes]" class="form-control form-control-sm" value="{{ $disease['notes'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.health_notes') }}</label>
                        <textarea name="health[health_notes]" class="form-control" rows="3">{{ old('health.health_notes') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.health_medical_records') }}</label>
                        <input type="file" name="health[health_medical_records][]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx">
                    </div>
                </div>
            </div>

            {{-- Step: attachments --}}
            <div class="beneficiary-panel" data-step="attachments" hidden>
                <h2 class="h5 mb-3">{{ __('beneficiary_registration.steps.attachments') }}</h2>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.profiles_national_images') }} <span class="text-danger">*</span></label>
                    <input type="file" name="profiles_national_images[]" class="form-control" multiple accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.profiles_family_cards') }} <span class="text-danger">*</span></label>
                    <input type="file" name="profiles_family_cards[]" class="form-control" multiple accept="image/*" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('beneficiary_registration.fields.profiles_attachments') }}</label>
                    <input type="file" name="profiles_attachments[]" class="form-control" multiple>
                </div>
            </div>

            <div class="d-flex justify-content-between gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary" id="wizardBackBtn" hidden>{{ __('beneficiary_registration.back') }}</button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="wizardNextBtn">{{ __('beneficiary_registration.next') }}</button>
                    <button type="submit" class="btn btn-success" id="wizardSubmitBtn" hidden>{{ __('beneficiary_registration.submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="diseaseRowTemplate">
    <div class="disease-row" data-disease-row>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small">{{ __('beneficiary_registration.fields.disease') }}</label>
                <select name="health[diseases][__INDEX__][disease]" class="form-select form-select-sm">
                    <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                    @foreach($diseases as $option)
                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('beneficiary_registration.fields.disease_type') }}</label>
                <select name="health[diseases][__INDEX__][type]" class="form-select form-select-sm">
                    <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                    @foreach($diseaseTypes as $option)
                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('beneficiary_registration.fields.treatment_type') }}</label>
                <select name="health[diseases][__INDEX__][treatment_type]" class="form-select form-select-sm">
                    <option value="">{{ __('beneficiary_registration.options.select') }}</option>
                    @foreach($treatmentTypes as $option)
                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-remove-disease>{{ __('beneficiary_registration.remove') }}</button>
            </div>
            <div class="col-md-6">
                <label class="form-label small">{{ __('beneficiary_registration.fields.treatment_cost') }}</label>
                <input type="text" name="health[diseases][__INDEX__][treatment_cost]" class="form-control form-control-sm">
            </div>
            <div class="col-md-6">
                <label class="form-label small">{{ __('beneficiary_registration.fields.disease_notes') }}</label>
                <input type="text" name="health[diseases][__INDEX__][notes]" class="form-control form-control-sm">
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('beneficiaryRegistrationForm');
    const extraTypeSelect = document.getElementById('extraTypeSelect');
    const stepsHost = document.getElementById('wizardSteps');
    const panels = Array.from(form.querySelectorAll('[data-step]'));
    const backBtn = document.getElementById('wizardBackBtn');
    const nextBtn = document.getElementById('wizardNextBtn');
    const submitBtn = document.getElementById('wizardSubmitBtn');
    const sickBlock = document.getElementById('sickHealthBlock');
    const maritalSelect = document.getElementById('maritalStatusSelect');
    const diseasesContainer = document.getElementById('diseasesContainer');
    const diseaseTemplate = document.getElementById('diseaseRowTemplate');
    let currentIndex = 0;
    let visibleSteps = [];

    function selectedGenderKey() {
        const checked = form.querySelector('.gender-radio:checked');
        return checked && checked.value === '2' ? 'female' : 'male';
    }

    function refreshMaritalLabels() {
        const key = selectedGenderKey();
        Array.from(maritalSelect.options).forEach((option) => {
            if (!option.value) return;
            option.textContent = option.dataset[key] || option.textContent;
        });
    }

    function refreshVisibleSteps() {
        const option = extraTypeSelect.selectedOptions[0];
        visibleSteps = (option?.dataset.steps || 'basic,health,attachments').split(',').filter(Boolean);
        currentIndex = 0;
        render();
    }

    function render() {
        stepsHost.innerHTML = '';
        visibleSteps.forEach((step, index) => {
            const pill = document.createElement('span');
            pill.className = 'step-pill' + (index === currentIndex ? ' active' : '') + (index < currentIndex ? ' done' : '');
            const panel = panels.find((p) => p.dataset.step === step);
            const title = panel?.querySelector('h2')?.textContent || step;
            pill.textContent = `${index + 1}. ${title}`;
            stepsHost.appendChild(pill);
        });

        panels.forEach((panel) => {
            const step = panel.dataset.step;
            const show = visibleSteps[currentIndex] === step;
            panel.hidden = !show;
        });

        backBtn.hidden = currentIndex === 0;
        const last = currentIndex >= visibleSteps.length - 1;
        nextBtn.hidden = last;
        submitBtn.hidden = !last;
    }

    function syncHealthVisibility() {
        const checked = form.querySelector('.health-status-radio:checked');
        const isSick = checked && checked.value === '2';
        sickBlock.hidden = !isSick;
        // Prevent empty disease fields from submitting when status is "healthy".
        sickBlock.querySelectorAll('input, select, textarea').forEach((el) => {
            el.disabled = !isSick;
        });
    }

    function reindexDiseases() {
        Array.from(diseasesContainer.querySelectorAll('[data-disease-row]')).forEach((row, index) => {
            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/health\[diseases\]\[\d+]/, `health[diseases][${index}]`);
            });
        });
    }

    document.getElementById('addDiseaseBtn')?.addEventListener('click', () => {
        const html = diseaseTemplate.innerHTML.replaceAll('__INDEX__', String(diseasesContainer.children.length));
        diseasesContainer.insertAdjacentHTML('beforeend', html);
        reindexDiseases();
    });

    diseasesContainer.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-disease]');
        if (!btn) return;
        const rows = diseasesContainer.querySelectorAll('[data-disease-row]');
        if (rows.length <= 1) return;
        btn.closest('[data-disease-row]')?.remove();
        reindexDiseases();
    });

    form.querySelectorAll('.gender-radio').forEach((el) => el.addEventListener('change', refreshMaritalLabels));
    form.querySelectorAll('.health-status-radio').forEach((el) => el.addEventListener('change', syncHealthVisibility));
    extraTypeSelect.addEventListener('change', refreshVisibleSteps);
    backBtn.addEventListener('click', () => {
        currentIndex = Math.max(0, currentIndex - 1);
        render();
    });
    nextBtn.addEventListener('click', () => {
        currentIndex = Math.min(visibleSteps.length - 1, currentIndex + 1);
        render();
    });

    refreshMaritalLabels();
    syncHealthVisibility();
    refreshVisibleSteps();
});
</script>
@endpush
