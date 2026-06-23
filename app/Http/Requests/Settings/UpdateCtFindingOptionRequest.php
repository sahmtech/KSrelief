<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\CtFindingOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCtFindingOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $option = $this->route('ct_finding_option');

        return $option instanceof CtFindingOption
            && ($this->user()?->can('update', $option) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CtFindingOption $option */
        $option = $this->route('ct_finding_option');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('ct_finding_options', 'code')->ignore($option->id)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
