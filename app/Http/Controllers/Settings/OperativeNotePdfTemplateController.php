<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateOperativeNotePdfTemplateRequest;
use App\Services\OperativeNotePdfTemplateService;
use App\Support\OperativeNotePdfTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OperativeNotePdfTemplateController extends Controller
{
    public function __construct(
        private readonly OperativeNotePdfTemplateService $templateService,
    ) {}

    public function edit(): View
    {
        $this->authorizePermission('operative_note_pdf.view');

        $template = $this->templateService->current();

        return view('pages.settings.operative-note-pdf.edit', [
            'template' => $template,
            'tokens' => OperativeNotePdfTemplateCatalog::availableTokens(),
            'canUpdate' => auth()->user()?->can('operative_note_pdf.update') ?? false,
        ]);
    }

    public function update(UpdateOperativeNotePdfTemplateRequest $request): RedirectResponse
    {
        $this->authorizePermission('operative_note_pdf.update');

        $this->templateService->save($request->validated(), $request->user());

        return redirect()
            ->route('settings.operative-note-pdf.edit')
            ->with('success', __('settings.entities.operative_note_pdf.messages.updated'));
    }

    public function reset(\Illuminate\Http\Request $request): RedirectResponse
    {
        $this->authorizePermission('operative_note_pdf.update');

        $this->templateService->resetToDefaults($request->user());

        return redirect()
            ->route('settings.operative-note-pdf.edit')
            ->with('success', __('settings.entities.operative_note_pdf.messages.reset'));
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
