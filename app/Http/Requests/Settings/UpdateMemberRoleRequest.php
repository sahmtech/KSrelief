<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\MemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MemberRole $memberRole */
        $memberRole = $this->route('member_role');

        return $this->user()?->can('update', $memberRole) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var MemberRole $memberRole */
        $memberRole = $this->route('member_role');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('member_roles', 'code')->ignore($memberRole->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
