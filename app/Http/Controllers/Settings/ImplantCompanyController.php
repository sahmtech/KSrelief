<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreImplantCompanyRequest;
use App\Http\Requests\Settings\UpdateImplantCompanyRequest;
use App\Models\ImplantCompany;
use App\Services\Settings\ImplantCompanySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImplantCompanyController extends Controller
{
    public function __construct(
        private readonly ImplantCompanySettingService $implantCompanySettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ImplantCompany::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.implant-companies.index', [
            'companies' => $this->implantCompanySettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ImplantCompany::class);

        return view('pages.settings.implant-companies.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreImplantCompanyRequest $request): RedirectResponse
    {
        $company = $this->implantCompanySettingService->create($request->validated());

        return redirect()
            ->route('settings.implant-companies.show', $company)
            ->with('success', __('settings.entities.implant_companies.messages.created'));
    }

    public function show(ImplantCompany $implantCompany): View
    {
        $this->authorize('view', $implantCompany);

        $implantCompany->load(['creator', 'updater', 'electrodeTypes']);

        return view('pages.settings.implant-companies.show', [
            'company' => $implantCompany,
        ]);
    }

    public function edit(ImplantCompany $implantCompany): View
    {
        $this->authorize('update', $implantCompany);

        $implantCompany->load('electrodeTypes');

        return view('pages.settings.implant-companies.edit', [
            'company' => $implantCompany,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateImplantCompanyRequest $request, ImplantCompany $implantCompany): RedirectResponse
    {
        $this->implantCompanySettingService->update($implantCompany, $request->validated());

        return redirect()
            ->route('settings.implant-companies.show', $implantCompany)
            ->with('success', __('settings.entities.implant_companies.messages.updated'));
    }

    public function destroy(ImplantCompany $implantCompany): RedirectResponse
    {
        $this->authorize('delete', $implantCompany);

        $this->implantCompanySettingService->delete($implantCompany);

        return redirect()
            ->route('settings.implant-companies.index')
            ->with('success', __('settings.entities.implant_companies.messages.deleted'));
    }
}
