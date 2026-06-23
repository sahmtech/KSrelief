<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreExpectationPostCiOptionRequest;
use App\Http\Requests\Settings\UpdateExpectationPostCiOptionRequest;
use App\Models\ExpectationPostCiOption;
use App\Services\Settings\ExpectationPostCiOptionSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpectationPostCiOptionController extends Controller
{
    public function __construct(
        private readonly ExpectationPostCiOptionSettingService $expectationPostCiOptionSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExpectationPostCiOption::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.expectation-post-ci-options.index', [
            'options' => $this->expectationPostCiOptionSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ExpectationPostCiOption::class);

        return view('pages.settings.expectation-post-ci-options.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreExpectationPostCiOptionRequest $request): RedirectResponse
    {
        $this->expectationPostCiOptionSettingService->create($request->validated());

        return redirect()
            ->route('settings.expectation-post-ci-options.index')
            ->with('success', __('settings.entities.expectation_post_ci_options.messages.created'));
    }

    public function show(ExpectationPostCiOption $expectationPostCiOption): View
    {
        $this->authorize('view', $expectationPostCiOption);

        $expectationPostCiOption->load(['creator', 'updater']);

        return view('pages.settings.expectation-post-ci-options.show', [
            'option' => $expectationPostCiOption,
        ]);
    }

    public function edit(ExpectationPostCiOption $expectationPostCiOption): View
    {
        $this->authorize('update', $expectationPostCiOption);

        return view('pages.settings.expectation-post-ci-options.edit', [
            'option' => $expectationPostCiOption,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateExpectationPostCiOptionRequest $request, ExpectationPostCiOption $expectationPostCiOption): RedirectResponse
    {
        $this->expectationPostCiOptionSettingService->update($expectationPostCiOption, $request->validated());

        return redirect()
            ->route('settings.expectation-post-ci-options.show', $expectationPostCiOption)
            ->with('success', __('settings.entities.expectation_post_ci_options.messages.updated'));
    }

    public function destroy(ExpectationPostCiOption $expectationPostCiOption): RedirectResponse
    {
        $this->authorize('delete', $expectationPostCiOption);

        $this->expectationPostCiOptionSettingService->delete($expectationPostCiOption);

        return redirect()
            ->route('settings.expectation-post-ci-options.index')
            ->with('success', __('settings.entities.expectation_post_ci_options.messages.deleted'));
    }
}
