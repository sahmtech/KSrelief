<?php

namespace App\Http\Requests\Campaign;

use App\Enums\SettingStatus;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeCampaignStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Campaign $campaign */
        $campaign = $this->route('campaign');

        return $this->user()?->can('changeStatus', $campaign) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'campaign_status_id' => ['required', 'integer', Rule::exists('campaign_statuses', 'id')->where('status', SettingStatus::Active->value)],
        ];
    }
}
