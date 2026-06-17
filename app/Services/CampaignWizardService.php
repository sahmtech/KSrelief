<?php

namespace App\Services;

/**
 * Future multi-step campaign setup orchestrator.
 *
 * Wizard steps (not implemented yet):
 * 1. Campaign Details
 * 2. Assign Members
 * 3. Patients Import
 * 4. Transportation Planning
 * 5. Activities Planning
 */
class CampaignWizardService
{
    public const STEP_DETAILS = 'details';

    public const STEP_MEMBERS = 'members';

    public const STEP_PATIENTS_IMPORT = 'patients_import';

    public const STEP_TRANSPORTATION = 'transportation';

    public const STEP_ACTIVITIES = 'activities';

    public function __construct(
        private readonly CampaignService $campaignService
    ) {}

    /**
     * @return array<string, array{order: int, label: string}>
     */
    public function steps(): array
    {
        return [
            self::STEP_DETAILS => [
                'order' => 1,
                'label' => __('campaigns.wizard.steps.details'),
            ],
            self::STEP_MEMBERS => [
                'order' => 2,
                'label' => __('campaigns.wizard.steps.members'),
            ],
            self::STEP_PATIENTS_IMPORT => [
                'order' => 3,
                'label' => __('campaigns.wizard.steps.patients_import'),
            ],
            self::STEP_TRANSPORTATION => [
                'order' => 4,
                'label' => __('campaigns.wizard.steps.transportation'),
            ],
            self::STEP_ACTIVITIES => [
                'order' => 5,
                'label' => __('campaigns.wizard.steps.activities'),
            ],
        ];
    }

    /**
     * Future: mark a wizard step as completed for a campaign.
     */
    public function completeStep(int $campaignId, string $step): void
    {
        // Audit: log campaign.wizard_step_completed event here in future.
    }
}
