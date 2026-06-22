<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignStatusRecord;
use App\Models\User;
use App\Support\RecordCodeGenerator;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function __construct(
        private readonly RecordCodeGenerator $codeGenerator,
    ) {}
    public function createCampaign(array $data, User $user): Campaign
    {
        return DB::transaction(function () use ($data, $user): Campaign {
            $campaign = Campaign::create([
                ...$this->withCampaignDays($data),
                'campaign_status_id' => $data['campaign_status_id'] ?? $this->defaultStatusId(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $campaign->update([
                'code' => $this->codeGenerator->generateCampaignCode($campaign),
            ]);

            return $campaign->load(['country', 'city', 'specialty', 'campaignStatus', 'creator']);
        });
    }

    public function updateCampaign(Campaign $campaign, array $data, User $user): Campaign
    {
        return DB::transaction(function () use ($campaign, $data, $user): Campaign {
            $campaign->update([
                ...$this->withCampaignDays($data),
                'updated_by' => $user->id,
            ]);

            return $campaign->fresh(['country', 'city', 'specialty', 'campaignStatus', 'creator', 'updater']);
        });
    }

    public function deleteCampaign(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign): void {
            $campaign->delete();
        });
    }

    public function changeStatus(Campaign $campaign, int $campaignStatusId, User $user): Campaign
    {
        return DB::transaction(function () use ($campaign, $campaignStatusId, $user): Campaign {
            $campaign->update([
                'campaign_status_id' => $campaignStatusId,
                'updated_by' => $user->id,
            ]);

            return $campaign->fresh(['country', 'city', 'specialty', 'campaignStatus', 'creator', 'updater']);
        });
    }

    /**
     * @return array<string, int>
     */
    public function getDashboardStats(): array
    {
        $today = now()->toDateString();
        $statusIds = CampaignStatusRecord::query()
            ->whereIn('code', ['active', 'completed', 'cancelled', 'draft', 'planned'])
            ->pluck('id', 'code');

        return [
            'total' => Campaign::count(),
            'active' => Campaign::where('campaign_status_id', $statusIds['active'] ?? 0)->count(),
            'completed' => Campaign::where('campaign_status_id', $statusIds['completed'] ?? 0)->count(),
            'cancelled' => Campaign::where('campaign_status_id', $statusIds['cancelled'] ?? 0)->count(),
            'upcoming' => Campaign::query()
                ->whereIn('campaign_status_id', array_filter([
                    $statusIds['draft'] ?? null,
                    $statusIds['planned'] ?? null,
                ]))
                ->whereDate('start_date', '>', $today)
                ->count(),
        ];
    }

    private function defaultStatusId(): int
    {
        return CampaignStatusRecord::query()
            ->active()
            ->where('is_default', true)
            ->value('id')
            ?? CampaignStatusRecord::query()->active()->where('code', 'draft')->value('id')
            ?? CampaignStatusRecord::query()->active()->orderBy('id')->value('id');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withCampaignDays(array $data): array
    {
        if (isset($data['start_date'], $data['end_date'])) {
            $data['shifts_count'] = Campaign::daysBetween($data['start_date'], $data['end_date']);
        }

        return $data;
    }
}
