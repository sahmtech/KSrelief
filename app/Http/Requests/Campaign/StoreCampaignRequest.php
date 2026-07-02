<?php

namespace App\Http\Requests\Campaign;

use App\Enums\SettingStatus;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Campaign::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->campaignRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function campaignRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'max:5000'],
            'target_group' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('status', SettingStatus::Active->value)],
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')
                    ->where('country_id', $this->input('country_id'))
                    ->where('status', SettingStatus::Active->value),
            ],
            'specialty_id' => ['required', 'integer', Rule::exists('specialties', 'id')->where('status', SettingStatus::Active->value)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'expected_patients' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'campaign_status_id' => ['nullable', 'integer', Rule::exists('campaign_statuses', 'id')->where('status', SettingStatus::Active->value)],
            'operation_defaults' => ['nullable', 'array'],
            'operation_defaults.*.electrode_type_id' => ['nullable', 'integer', 'exists:implant_electrode_types,id'],
            'operation_defaults.*.insertion_approach_id' => ['nullable', 'integer', 'exists:insertion_approaches,id'],
            'operation_defaults.*.insertion_depth' => ['nullable', 'array'],
            'operation_defaults.*.insertion_depth.selection' => ['nullable', 'string', 'max:80'],
            'operation_defaults.*.insertion_depth.note' => ['nullable', 'string', 'max:1000'],
            'operation_defaults.*.intra_op_findings' => ['nullable', 'string', 'max:80'],
            'operation_defaults.*.audio_test' => ['nullable', 'array'],
            'operation_defaults.*.audio_test.metrics' => ['nullable', 'array'],
            'operation_defaults.*.audio_test.metrics.*.key' => ['nullable', 'string', 'max:120'],
            'operation_defaults.*.audio_test.metrics.*.value' => ['nullable', 'string', 'max:500'],
        ];
    }
}
