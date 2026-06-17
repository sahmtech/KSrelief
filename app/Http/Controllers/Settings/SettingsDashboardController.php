<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsDashboardService;
use Illuminate\View\View;

class SettingsDashboardController extends Controller
{
    public function __construct(
        private readonly SettingsDashboardService $dashboardService
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        return view('pages.settings.dashboard', [
            'cards' => $this->dashboardService->getCards(),
        ]);
    }
}
