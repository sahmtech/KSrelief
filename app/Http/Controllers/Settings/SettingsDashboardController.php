<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\RecordCodeBackfillService;
use App\Services\Settings\SettingsDashboardService;
use Illuminate\View\View;

class SettingsDashboardController extends Controller
{
    public function __construct(
        private readonly SettingsDashboardService $dashboardService,
        private readonly RecordCodeBackfillService $recordCodeBackfillService,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $debugBackfill = null;

        if (config('app.debug')) {
            $debugBackfill = $this->recordCodeBackfillService->missingCounts();
        }

        return view('pages.settings.dashboard', [
            'cards' => $this->dashboardService->getCards(),
            'debugBackfill' => $debugBackfill,
        ]);
    }
}
