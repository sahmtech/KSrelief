@props([
    'member' => null,
    'memberRoles' => [],
    'specialties' => [],
    'statuses' => [],
    'users' => [],
])

<div class="row g-3">
    <div class="col-lg-6">
        <x-card :title="__('members.sections.personal')">
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('members.fields.first_name')" name="first_name" :value="old('first_name', $member?->first_name)" :placeholder="__('members.placeholders.first_name')" required />
                </div>
                <div class="col-md-6 ps-md-2">
                    <x-form-input :label="__('members.fields.last_name')" name="last_name" :value="old('last_name', $member?->last_name)" :placeholder="__('members.placeholders.last_name')" required />
                </div>
            </div>
            <div class="row g-0">
                <div class="col-md-6 pe-md-2">
                    <x-form-input :label="__('members.fields.gender')" name="gender" type="select">
                        <option value="">{{ __('members.fields.gender') }}</option>
                        @foreach(\App\Enums\Gender::cases() as $gender)
                            <option value="{{ $gender->value }}" @selected(old('gender', $member?->gender?->value) === $gender->value)>
                                {{ $gender->label() }}
                            </option>
                        @endforeach
                    </x-form-input>
                </div>
                <div class="col-md-6 ps-md-2">
                    <x-form-input :label="__('members.fields.date_of_birth')" name="date_of_birth" type="date" :value="old('date_of_birth', $member?->date_of_birth?->format('Y-m-d'))" />
                </div>
            </div>
            <x-form-input :label="__('members.fields.nationality')" name="nationality" :value="old('nationality', $member?->nationality)" :placeholder="__('members.placeholders.nationality')" />
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('members.sections.contact')">
            <x-form-input :label="__('members.fields.mobile')" name="mobile" :value="old('mobile', $member?->mobile)" :placeholder="__('members.placeholders.mobile')" required />
            <x-form-input :label="__('members.fields.email')" name="email" type="email" :value="old('email', $member?->email)" :placeholder="__('members.placeholders.email')" />
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('members.sections.professional')">
            <x-form-input :label="__('members.fields.role')" name="member_role_id" type="select" required>
                <option value="">{{ __('members.placeholders.select_role') }}</option>
                @foreach($memberRoles as $role)
                    <option value="{{ $role->id }}" @selected((string) old('member_role_id', $member?->member_role_id) === (string) $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </x-form-input>
            <x-form-input :label="__('members.fields.specialty')" name="specialty_id" type="select">
                <option value="">{{ __('members.placeholders.select_specialty') }}</option>
                @foreach($specialties as $specialty)
                    <option value="{{ $specialty->id }}" @selected((string) old('specialty_id', $member?->specialty_id) === (string) $specialty->id)>
                        {{ $specialty->name }}
                    </option>
                @endforeach
            </x-form-input>
            <x-form-input :label="__('members.fields.status')" name="status" type="select" required>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $member?->status?->value ?? 'active') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </x-form-input>
            <x-form-input :label="__('members.fields.notes')" name="notes" type="textarea" :value="old('notes', $member?->notes)" :placeholder="__('members.placeholders.notes')" />
        </x-card>
    </div>

    <div class="col-lg-6">
        <x-card :title="__('members.sections.account')">
            <x-form-input :label="__('members.fields.user_account')" name="user_id" type="select">
                <option value="">{{ __('members.placeholders.select_user') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id', $member?->user_id) === (string) $user->id)>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </x-form-input>
            <p class="text-muted mb-0" style="font-size: 0.8125rem;">
                <i class="ti ti-info-circle me-1"></i>
                {{ __('members.fields.user_account') }}
            </p>
        </x-card>
    </div>
</div>
