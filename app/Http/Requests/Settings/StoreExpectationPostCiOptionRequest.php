<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ExpectationPostCiOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpectationPostCiOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExpectationPostCiOption::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('expectation_post_ci_options', 'code')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
