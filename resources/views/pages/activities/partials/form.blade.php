@props([
    'activity' => null,
    'campaigns' => [],
    'activityTypes' => [],
    'patientStages' => [],
    'selectedCampaignId' => null,
    'prefillDate' => null,
    'prefillStart' => null,
    'prefillEnd' => null,
])

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('activities.fields.campaign') }} <span class="text-danger">*</span></label>
        <select name="campaign_id" class="form-select @error('campaign_id') is-invalid @enderror" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}" @selected((string) old('campaign_id', $activity?->campaign_id ?? $selectedCampaignId) === (string) $campaign->id)>{{ $campaign->name }}</option>
            @endforeach
        </select>
        @error('campaign_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <x-form-input :label="__('activities.fields.type')" name="activity_type_id" type="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($activityTypes as $type)
                <option value="{{ $type->id }}" @selected((string) old('activity_type_id', $activity?->activity_type_id) === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </x-form-input>
    </div>
    <div class="col-12">
        <x-form-input :label="__('activities.fields.title')" name="title" type="text" :value="old('title', $activity?->title)" required />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('activities.fields.date')" name="activity_date" type="date"
                      :value="old('activity_date', $activity?->activity_date?->format('Y-m-d') ?? $prefillDate ?? date('Y-m-d'))" required />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('activities.fields.start_time')" name="start_time" type="time"
                      :value="old('start_time', ($activity && $activity->startTimeLabel() !== '00:00') ? $activity->startTimeLabel() : ($prefillStart ?? ''))" required />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('activities.fields.end_time')" name="end_time" type="time"
                      :value="old('end_time', ($activity && $activity->endTimeLabel() !== '00:00') ? $activity->endTimeLabel() : ($prefillEnd ?? ''))" required />
    </div>
    <div class="col-md-6">
        <x-form-input :label="__('activities.fields.location')" name="location" type="text" :value="old('location', $activity?->location)" />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('activities.fields.max_participants')" name="max_participants" type="number" min="1" :value="old('max_participants', $activity?->max_participants)" />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('activities.fields.workflow_stage')" name="patient_stage_id" type="select">
            <option value="">{{ __('common.select') }}</option>
            @foreach($patientStages as $stage)
                <option value="{{ $stage->id }}" @selected((string) old('patient_stage_id', $activity?->patient_stage_id) === (string) $stage->id)>{{ $stage->name }}</option>
            @endforeach
        </x-form-input>
    </div>
    <div class="col-12">
        <x-form-input :label="__('activities.fields.description')" name="description" type="textarea" :value="old('description', $activity?->description)" />
    </div>
</div>
