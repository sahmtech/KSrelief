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
            'shifts_count' => ['required', 'integer', 'min:1', 'max:99'],
            'expected_patients' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:10000'],
            'campaign_status_id' => ['nullable', 'integer', Rule::exists('campaign_statuses', 'id')->where('status', SettingStatus::Active->value)],
        ];
    }
}
