<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\CampaignStatusRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CampaignStatusRecord $campaignStatus */
        $campaignStatus = $this->route('campaign_status');

        return $this->user()?->can('update', $campaignStatus) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CampaignStatusRecord $campaignStatus */
        $campaignStatus = $this->route('campaign_status');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('campaign_statuses', 'code')->ignore($campaignStatus->id)],
            'color' => ['required', 'string', 'max:20'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
