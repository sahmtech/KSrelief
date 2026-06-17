<?php

namespace App\Http\Requests\Member;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Enums\SettingStatus;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Member::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->memberRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function memberRules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'max:30', Rule::unique('members', 'mobile')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('members', 'email')],
            'gender' => ['nullable', Rule::in(Gender::values())],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'member_role_id' => [
                'required',
                'integer',
                Rule::exists('member_roles', 'id')->where('status', SettingStatus::Active->value),
            ],
            'specialty_id' => [
                'nullable',
                'integer',
                Rule::exists('specialties', 'id')->where('status', SettingStatus::Active->value),
            ],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('members', 'user_id'),
            ],
            'status' => ['required', Rule::in(MemberStatus::values())],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
