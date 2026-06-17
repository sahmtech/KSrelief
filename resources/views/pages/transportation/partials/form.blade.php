@props([
    'trip' => null,
    'campaigns' => [],
    'locations' => [],
    'tripTypes' => [],
    'selectedCampaignId' => null,
    'formAction' => '',
])

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('transportation.fields.campaign') }} <span class="text-danger">*</span></label>
        <select name="campaign_id" id="tripCampaignSelect" class="form-select @error('campaign_id') is-invalid @enderror" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}" @selected((string) old('campaign_id', $trip?->campaign_id ?? $selectedCampaignId) === (string) $campaign->id)>
                    {{ $campaign->name }}
                </option>
            @endforeach
        </select>
        @error('campaign_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('transportation.fields.trip_date')" name="trip_date" type="date"
                      :value="old('trip_date', $trip?->trip_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('transportation.fields.trip_type')" name="trip_type" type="select" required>
            @foreach($tripTypes as $type)
                <option value="{{ $type->value }}" @selected(old('trip_type', $trip?->trip_type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-form-input>
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('transportation.fields.departure_time')" name="departure_time" type="time"
                      :value="old('departure_time', ($trip && $trip->departureTimeLabel() !== '—') ? $trip->departureTimeLabel() : '')" required />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('transportation.fields.arrival_time')" name="arrival_time" type="time"
                      :value="old('arrival_time', ($trip && $trip->arrivalTimeLabel() !== '—') ? $trip->arrivalTimeLabel() : '')" />
    </div>
    <div class="col-md-3">
        <x-transportation-location-select
            name="from_location_id"
            :label="__('transportation.fields.from_location')"
            :selected-id="old('from_location_id', $trip?->from_location_id)"
            :selected-label="$trip?->fromLocation ? $trip->fromLocation->name.' ('.__('settings.transportation_types.'.$trip->fromLocation->type).')' : null"
            :locations="collect($locations)"
            required
        />
    </div>
    <div class="col-md-3">
        <x-transportation-location-select
            name="to_location_id"
            :label="__('transportation.fields.to_location')"
            :selected-id="old('to_location_id', $trip?->to_location_id)"
            :selected-label="$trip?->toLocation ? $trip->toLocation->name.' ('.__('settings.transportation_types.'.$trip->toLocation->type).')' : null"
            :locations="collect($locations)"
            required
        />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('transportation.fields.vehicle_number')" name="vehicle_number" type="text"
                      :value="old('vehicle_number', $trip?->vehicle_number)" />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('transportation.fields.driver_name')" name="driver_name" type="text"
                      :value="old('driver_name', $trip?->driver_name)" />
    </div>
    <div class="col-md-4">
        <x-form-input :label="__('transportation.fields.capacity')" name="capacity" type="number" min="1"
                      :value="old('capacity', $trip?->capacity)" />
    </div>
    <div class="col-12">
        <x-form-input :label="__('transportation.fields.notes')" name="notes" type="textarea"
                      :value="old('notes', $trip?->notes)" />
    </div>
</div>

@push('scripts')
<script>
document.getElementById('tripCampaignSelect')?.addEventListener('change', function () {
    const id = this.value;
    if (!id) return;
    const base = @json($formAction);
    window.location.href = base + (base.includes('?') ? '&' : '?') + 'campaign_id=' + id;
});
</script>
@endpush
