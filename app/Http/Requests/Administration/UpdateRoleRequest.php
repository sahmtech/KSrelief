<?php

namespace App\Http\Requests\Administration;

use App\Enums\SystemRole;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('role.update') ?? false;
    }

    public function isSystemRole(): bool
    {
        $role = $this->route('role');

        return $role instanceof \App\Models\Role
            && in_array($role->name, SystemRole::values(), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(PermissionRegistry::all())],
        ];

        if (! $this->isSystemRole()) {
            $rules['name'] = [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('roles', 'name')
                    ->where('guard_name', PermissionRegistry::GUARD)
                    ->ignore($role?->id),
            ];
        }

        return $rules;
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
            'name.prohibited' => __('pages.roles.validation.system_name_locked'),
            'permissions.array' => __('pages.roles.validation.permissions_invalid'),
            'permissions.*.in' => __('pages.roles.validation.permission_invalid'),
        ];
    }
}
