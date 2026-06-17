<?php

namespace App\Http\Requests\Administration;

use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('role.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')->where('guard_name', PermissionRegistry::GUARD),
                Rule::notIn(\App\Enums\SystemRole::values()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionRegistry::all())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('pages.roles.validation.name_required'),
            'name.regex' => __('pages.roles.validation.name_format'),
            'name.unique' => __('pages.roles.validation.name_unique'),
            'name.not_in' => __('pages.roles.validation.system_name_reserved'),
            'permissions.array' => __('pages.roles.validation.permissions_invalid'),
            'permissions.*.in' => __('pages.roles.validation.permission_invalid'),
        ];
    }
}
