<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\CtFindingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCtFindingOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CtFindingOption::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('ct_finding_options', 'code')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
