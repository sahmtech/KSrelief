<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\CampaignStatusRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CampaignStatusRecord::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('campaign_statuses', 'code')],
            'color' => ['required', 'string', 'max:20'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
