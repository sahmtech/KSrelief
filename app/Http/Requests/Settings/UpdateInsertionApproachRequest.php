<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\InsertionApproach;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInsertionApproachRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var InsertionApproach $approach */
        $approach = $this->route('insertion_approach');

        return $this->user()?->can('update', $approach) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var InsertionApproach $approach */
        $approach = $this->route('insertion_approach');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('insertion_approaches', 'code')->ignore($approach->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
