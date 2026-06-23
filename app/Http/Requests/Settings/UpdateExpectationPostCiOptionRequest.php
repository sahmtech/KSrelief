<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ExpectationPostCiOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpectationPostCiOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('expectation_post_ci_option');

        return $option instanceof ExpectationPostCiOption
            && ($this->user()?->can('update', $option) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ExpectationPostCiOption $option */
        $option = $this->route('expectation_post_ci_option');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('expectation_post_ci_options', 'code')->ignore($option->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
