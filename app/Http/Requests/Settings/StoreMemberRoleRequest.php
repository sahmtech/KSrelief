<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\MemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MemberRole::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('member_roles', 'code')],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
