<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class AdminMenu
{
    /**
     * Build sidebar menu filtered by the authenticated user's permissions.
     *
     * @return list<array<string, mixed>>
     */
    public static function build(): array
    {
        $user = Auth::user();

        return collect(config('admin.menu'))
            ->map(function (array $item) use ($user): ?array {
                if ($item['key'] === 'reports' && ! config('admin.show_reports', true)) {
                    return null;
                }

                $built = [
                    'key' => $item['key'],
                    'label' => __("menu.{$item['key']}"),
                    'icon' => $item['icon'],
                ];

                if (isset($item['route'])) {
                    $built['route'] = $item['route'];
                }

                if (isset($item['permission'])) {
                    $built['permission'] = $item['permission'];
                }

                if (isset($item['children'])) {
                    $built['children'] = collect($item['children'])
                        ->filter(function (array $child) use ($user): bool {
                            if (! isset($child['permission'])) {
                                return true;
                            }

                            return $user?->can($child['permission']) ?? false;
                        })
                        ->map(fn (array $child): array => [
                            'key' => $child['key'],
                            'label' => __("menu.{$child['key']}"),
                            'route' => $child['route'],
                            'permission' => $child['permission'] ?? null,
                        ])
                        ->values()
                        ->all();

                    if (count($built['children']) === 0) {
                        return null;
                    }

                    if (count($built['children']) === 1) {
                        $child = $built['children'][0];

                        return [
                            'key' => $child['key'],
                            'label' => $child['label'],
                            'icon' => $item['icon'],
                            'route' => $child['route'],
                        ];
                    }
                }

                if (isset($item['gate'])) {
                    if (! ($user && \Illuminate\Support\Facades\Gate::allows($item['gate']))) {
                        return null;
                    }
                } elseif (isset($built['permission'])) {
                    if (! ($user?->can($built['permission']) ?? false)) {
                        return null;
                    }
                }

                return $built;
            })
            ->filter()
            ->values()
            ->all();
    }
}
