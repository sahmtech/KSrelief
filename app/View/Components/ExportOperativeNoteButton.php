<?php

namespace App\View\Components;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\MedicalRecordFieldPresenter;
use App\Support\OperationFieldResolver;
use App\Support\ScreeningFieldSupport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ExportOperativeNoteButton extends Component
{
    /** @var Collection<int, array<string, mixed>> */
    public Collection $operations;

    public ?array $latest = null;

    public string $modalId;

    public function __construct(
        public Patient $patient,
        public string $size = 'sm',
    ) {
        $this->modalId = 'exportOperativeNoteModal-'.$patient->id;
        $this->operations = collect();

        $user = auth()->user();
        if (! $user || ! $user->can('viewAny', [MedicalRecord::class, $patient])) {
            return;
        }

        $records = MedicalRecord::query()
            ->where('patient_id', $patient->id)
            ->whereHas('stage', fn ($q) => $q->where('code', 'operation'))
            ->with(['submitter'])
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get();

        $this->operations = $records->map(fn (MedicalRecord $record): array => $this->mapRecord($record))->values();
        $this->latest = $this->operations->first();
    }

    public function shouldRender(): bool
    {
        return $this->operations->isNotEmpty();
    }

    public function render(): View
    {
        return view('components.export-operative-note-button');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapRecord(MedicalRecord $record): array
    {
        $operationDate = $record->field('operation_date') ?: $record->record_date?->format('Y-m-d');
        $surgeon = MedicalRecordFieldPresenter::display('surgeon', $record->field('surgeon'), ['type' => 'member_select']);
        if ($surgeon === '—') {
            $surgeon = '';
        }

        $sideCode = (string) ($record->field('side_of_surgery') ?: $this->patient->surgical_side ?: '');
        $sideLabel = filled($sideCode)
            ? ScreeningFieldSupport::optionLabel($sideCode, 'operation_side_of_surgery_options')
            : '';
        if ($sideLabel === $sideCode && filled($sideCode)) {
            $sideLabel = (string) __('workflow.sides.'.$sideCode);
        }

        $company = OperationFieldResolver::resolve(
            'implant_company_id',
            $record->field('implant_company_id'),
            ['type' => 'company_select']
        )['text'] ?? '';
        if ($company === '—') {
            $company = '';
        }

        $implantType = OperationFieldResolver::resolve(
            'electrode_type_id',
            $record->field('electrode_type_id'),
            ['type' => 'electrode_select']
        )['text'] ?? '';
        if ($implantType === '—' || $implantType === '') {
            $implantType = filled($record->field('electrode_type'))
                ? (string) $record->field('electrode_type')
                : '';
        }

        return [
            'id' => $record->id,
            'date' => filled($operationDate)
                ? \Illuminate\Support\Carbon::parse((string) $operationDate)->format('d M Y')
                : '—',
            'surgeon' => $surgeon !== '' ? $surgeon : '—',
            'side' => filled($sideLabel) ? $sideLabel : '—',
            'company' => $company !== '' ? $company : '—',
            'implant_type' => $implantType !== '' ? $implantType : '—',
            'export_url' => route('patients.records.export-operation-pdf', [$this->patient, $record]),
        ];
    }
}
