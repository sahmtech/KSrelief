<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kpis' => DashboardKpiResource::make($this->resource)->resolve(),
            'charts' => collect($this->resource['charts'] ?? [])->map(fn (array $chart) => [
                'title' => $chart['title'],
                'config' => $chart['config'],
            ])->values()->all(),
            'recent' => collect($this->resource['recentFeed'] ?? [])->map(fn (array $item) => [
                'type' => $item['type'],
                'title' => $item['title'],
                'meta' => $item['meta'],
                'at' => $item['at']?->toIso8601String(),
                'url' => $item['url'] ?? null,
            ])->values()->all(),
            'upcoming' => [
                'activities' => collect($this->resource['upcoming']['activities'] ?? [])->map(fn ($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'date' => $a->activity_date?->toDateString(),
                ]),
                'trips' => collect($this->resource['upcoming']['trips'] ?? [])->map(fn ($t) => [
                    'id' => $t->id,
                    'code' => $t->trip_code,
                    'date' => $t->trip_date?->toDateString(),
                ]),
                'campaigns' => collect($this->resource['upcoming']['campaigns'] ?? [])->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'start_date' => $c->start_date?->toDateString(),
                ]),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
