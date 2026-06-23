<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\MriFindingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMriFindingOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('mri_finding_option');

        return $option instanceof MriFindingOption
            && ($this->user()?->can('update', $option) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var MriFindingOption $option */
        $option = $this->route('mri_finding_option');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('mri_finding_options', 'code')->ignore($option->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
