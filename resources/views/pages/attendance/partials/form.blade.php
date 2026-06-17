@props([
    'attendance' => null,
    'campaigns' => [],
    'members' => [],
    'attendanceStatuses' => [],
    'selectedCampaignId' => null,
    'formAction' => '',
])

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ __('attendance.fields.campaign') }} <span class="text-danger">*</span></label>
        <select name="campaign_id" id="attendanceCampaignSelect" class="form-select @error('campaign_id') is-invalid @enderror" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($campaigns as $campaign)
                <option value="{{ $campaign->id }}" @selected((string) old('campaign_id', $attendance?->campaign_id ?? $selectedCampaignId) === (string) $campaign->id)>
                    {{ $campaign->name }}
                </option>
            @endforeach
        </select>
        @error('campaign_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('attendance.fields.date')" name="attendance_date" type="date"
                      :value="old('attendance_date', $attendance?->attendance_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('attendance.fields.shift')" name="shift_number" type="number" min="1" max="10"
                      :value="old('shift_number', $attendance?->shift_number ?? 1)" required />
    </div>
    <div class="col-md-6">
        <x-form-input :label="__('attendance.fields.member')" name="member_id" type="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($members as $member)
                <option value="{{ $member->id }}" @selected((string) old('member_id', $attendance?->member_id) === (string) $member->id)>
                    {{ $member->full_name }} — {{ $member->memberRole?->name }}
                </option>
            @endforeach
        </x-form-input>
    </div>
    <div class="col-md-6">
        <x-form-input :label="__('attendance.fields.status')" name="attendance_status_id" type="select" required>
            <option value="">{{ __('common.select') }}</option>
            @foreach($attendanceStatuses as $status)
                <option value="{{ $status->id }}" @selected((string) old('attendance_status_id', $attendance?->attendance_status_id) === (string) $status->id)>
                    {{ $status->name }}
                </option>
            @endforeach
        </x-form-input>
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('attendance.fields.check_in')" name="check_in" type="time"
                      :value="old('check_in', ($attendance && $attendance->checkInLabel() !== '—') ? $attendance->checkInLabel() : '')" />
    </div>
    <div class="col-md-3">
        <x-form-input :label="__('attendance.fields.check_out')" name="check_out" type="time"
                      :value="old('check_out', ($attendance && $attendance->checkOutLabel() !== '—') ? $attendance->checkOutLabel() : '')" />
    </div>
    <div class="col-12">
        <x-form-input :label="__('attendance.fields.notes')" name="notes" type="textarea"
                      :value="old('notes', $attendance?->notes)" />
    </div>
</div>

@push('scripts')
<script>
document.getElementById('attendanceCampaignSelect')?.addEventListener('change', function () {
    const id = this.value;
    if (!id) return;
    const base = @json($formAction);
    window.location.href = base + (base.includes('?') ? '&' : '?') + 'campaign_id=' + id;
});
</script>
@endpush
