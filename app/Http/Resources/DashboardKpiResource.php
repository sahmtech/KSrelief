<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardKpiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview' => $this->resource['stats']['overview'] ?? [],
            'campaigns' => $this->resource['stats']['campaigns'] ?? [],
            'patients' => $this->resource['stats']['patients'] ?? [],
            'workflow' => $this->resource['stats']['workflow'] ?? [],
            'members' => $this->resource['stats']['members'] ?? [],
            'attendance' => $this->resource['stats']['attendance'] ?? [],
            'transportation' => $this->resource['stats']['transportation'] ?? [],
            'activities' => $this->resource['stats']['activities'] ?? [],
            'imports' => $this->resource['stats']['imports'] ?? [],
        ];
    }
}
