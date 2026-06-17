<?php

namespace App\Http\Requests\Campaign;

use App\Models\Campaign;

class UpdateCampaignRequest extends StoreCampaignRequest
{
    public function authorize(): bool
    {
        $campaign = $this->route('campaign');

        return $campaign instanceof Campaign
            && ($this->user()?->can('update', $campaign) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->campaignRules();
    }
}
