<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\RecordCodeBackfillService;
use Illuminate\Http\RedirectResponse;

class RecordCodeBackfillController extends Controller
{
    public function __construct(
        private readonly RecordCodeBackfillService $backfillService,
    ) {}

    public function store(): RedirectResponse
    {
        abort_unless(config('app.debug'), 404);
        abort_unless(auth()->user()?->can('settings.view'), 403);

        $result = $this->backfillService->backfill();

        return redirect()
            ->route('settings.dashboard')
            ->with('success', __('settings.debug.backfill_success', [
                'campaigns' => $result['campaigns'],
                'patients' => $result['patients'],
            ]));
    }
}
