<?php

namespace App\Http\Requests\Patient;

use App\Services\PatientAccessService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiExportPatientsRequest extends ExportPatientsRequest
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'campaign_id.required' => __('patients.api.export.campaign_required'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if (! $user) {
            return;
        }

        $merged = [];
        $access = app(PatientAccessService::class);

        if (! $this->filled('campaign_id')) {
            $fromQuery = $this->query('campaign_id');

            if (filled($fromQuery)) {
                $merged['campaign_id'] = (int) $fromQuery;
            } else {
                $defaultId = $access->defaultExportCampaignId($user);

                if ($defaultId !== null) {
                    $merged['campaign_id'] = $defaultId;
                }
            }
        }

        if (! $this->has('include_all_stages') && ! $this->filled('stages')) {
            $merged['include_all_stages'] = true;
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        $user = $this->user();
        $campaigns = $user
            ? app(PatientAccessService::class)->exportableCampaigns($user)
            : collect();

        throw new HttpResponseException(response()->json([
            'message' => __('patients.api.export.validation_failed'),
            'errors' => $validator->errors(),
            'available_campaigns' => $campaigns->map(fn ($campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'code' => $campaign->code,
            ])->values(),
            'defaults' => [
                'include_all_stages' => true,
            ],
        ], 422));
    }
}
