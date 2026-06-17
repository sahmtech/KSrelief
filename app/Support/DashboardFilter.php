<?php

namespace App\Support;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class DashboardFilter
{
    /**
     * @param  list<int>|null  $scopedCampaignIds  Null means no campaign restriction.
     */
    public function __construct(
        public ?int $campaignId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?int $specialtyId = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?array $scopedCampaignIds = null,
    ) {}

    public static function fromRequest(Request $request, ?array $scopedCampaignIds = null): self
    {
        return new self(
            campaignId: $request->integer('campaign_id') ?: null,
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
            specialtyId: $request->integer('specialty_id') ?: null,
            countryId: $request->integer('country_id') ?: null,
            cityId: $request->integer('city_id') ?: null,
            scopedCampaignIds: $scopedCampaignIds,
        );
    }

    public function forCampaign(int $campaignId): self
    {
        return new self(
            campaignId: $campaignId,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            specialtyId: $this->specialtyId,
            countryId: $this->countryId,
            cityId: $this->cityId,
            scopedCampaignIds: $this->scopedCampaignIds,
        );
    }

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->campaignId,
            $this->dateFrom,
            $this->dateTo,
            $this->specialtyId,
            $this->countryId,
            $this->cityId,
            $this->scopedCampaignIds,
        ]));
    }

    /** @return list<int>|null Resolved campaign IDs for scoped queries. */
    public function resolvedCampaignIds(): ?array
    {
        if ($this->campaignId) {
            if ($this->scopedCampaignIds !== null && ! in_array($this->campaignId, $this->scopedCampaignIds, true)) {
                return [];
            }

            return [$this->campaignId];
        }

        if ($this->scopedCampaignIds !== null) {
            $ids = $this->scopedCampaignIds;

            if ($this->specialtyId || $this->countryId || $this->cityId) {
                $query = Campaign::query()->whereIn('id', $ids);
                $this->applyCampaignAttributes($query);

                return $query->pluck('id')->all();
            }

            return $ids;
        }

        if ($this->specialtyId || $this->countryId || $this->cityId) {
            $query = Campaign::query();
            $this->applyCampaignAttributes($query);

            return $query->pluck('id')->all();
        }

        return null;
    }

    public function applyCampaignQuery(Builder $query): Builder
    {
        $ids = $this->resolvedCampaignIds();

        if ($ids !== null) {
            $query->whereIn('query_table.id', $ids);
        }

        if ($this->campaignId) {
            $query->where('query_table.id', $this->campaignId);
        }

        return $this->applyCampaignAttributes($query);
    }

    public function applyCampaignAttributes(Builder $query): Builder
    {
        if ($this->specialtyId) {
            $query->where('specialty_id', $this->specialtyId);
        }

        if ($this->countryId) {
            $query->where('country_id', $this->countryId);
        }

        if ($this->cityId) {
            $query->where('city_id', $this->cityId);
        }

        return $query;
    }

    public function applyDateRange(Builder $query, string $column = 'created_at'): Builder
    {
        if ($this->dateFrom) {
            $query->whereDate($column, '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate($column, '<=', $this->dateTo);
        }

        return $query;
    }
}
