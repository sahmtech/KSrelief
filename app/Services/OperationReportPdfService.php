<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\MedicalRecordFieldPresenter;
use App\Support\OperationFieldResolver;
use App\Support\OperationFieldSupport;
use App\Support\ScreeningFieldSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

final class OperationReportPdfService
{
    public function __construct(
        private readonly OperativeNotePdfTemplateService $templateService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildPayload(Patient $patient, MedicalRecord $record): array
    {
        $template = $this->templateService->current();
        $defaults = $template['defaults'];
        $formatting = $template['formatting'];

        $sideCode = (string) ($record->field('side_of_surgery') ?: $patient->surgical_side ?: '');
        $sideLabel = filled($sideCode)
            ? ScreeningFieldSupport::optionLabel($sideCode, 'operation_side_of_surgery_options')
            : '';

        if ($sideLabel === $sideCode && filled($sideCode)) {
            $sideLabel = (string) __('workflow.sides.'.$sideCode);
        }

        $implantType = $this->lookupText('electrode_type_id', $record->field('electrode_type_id'), 'electrode_select');
        if ($implantType === '' && filled($record->field('electrode_type'))) {
            $implantType = (string) $record->field('electrode_type');
        }

        $insertionThrough = $this->lookupText(
            'insertion_approach_id',
            $record->field('insertion_approach_id'),
            'insertion_approach_select'
        );
        if ($insertionThrough === '' && filled($record->field('insertion_type'))) {
            $insertionThrough = (string) $record->field('insertion_type');
        }

        $surgeon = MedicalRecordFieldPresenter::display(
            'surgeon',
            $record->field('surgeon'),
            ['type' => 'member_select']
        );
        if ($surgeon === '—') {
            $surgeon = '';
        }

        $insertionDepth = OperationFieldSupport::presentInsertionDepth($record->field('insertion_depth'));
        if ($insertionDepth === '—') {
            $insertionDepth = '';
        }

        $audioMetrics = collect(OperationFieldSupport::normalizeAudioTest($record->field('audio_test'))['metrics'] ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): array => [
                'key' => (string) ($row['key'] ?? ''),
                'value' => (string) ($row['value'] ?? ''),
            ])
            ->values()
            ->all();

        $audioMetricsText = collect($audioMetrics)
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->implode('; ');

        $operationDate = $record->field('operation_date') ?: $record->record_date?->format('Y-m-d');
        $formattedDate = filled($operationDate)
            ? Carbon::parse((string) $operationDate)->format('d/m/Y')
            : '';

        $sidePart = filled($sideLabel) ? strtolower((string) $sideLabel) : '';
        $surgeryLine = $sidePart !== '' ? $sidePart.' cochlear implant' : '';

        $timeOut = trim((string) ($record->field('time_out_surgery') ?? ''));

        $logoSrc = null;
        if (! empty($formatting['show_logo'])) {
            $logoPath = public_path('images/ksrelief-logo-horizontal.jpg');
            if (! is_file($logoPath)) {
                $logoPath = public_path('images/ksrelief-logo-horizontal.png');
            }
            if (is_file($logoPath)) {
                $mime = str_ends_with(strtolower($logoPath), '.jpg') ? 'image/jpeg' : 'image/png';
                $logoSrc = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($logoPath));
            }
        }

        $tokenValues = [
            '{{local_anesthesia}}' => (string) ($defaults['local_anesthesia'] ?? ''),
            '{{bed_status}}' => (string) ($defaults['bed_status'] ?? ''),
            '{{insertion_depth}}' => $insertionDepth,
            '{{audio_metrics}}' => $audioMetricsText !== '' ? '— '.$audioMetricsText : '',
        ];

        $narrativeHtml = collect($template['narrative_paragraphs'])
            ->map(fn (string $paragraph): string => $this->templateService->renderParagraph($paragraph, $tokenValues))
            ->all();

        return [
            'logo_src' => $logoSrc,
            'operation_date' => $formattedDate,
            'surgeon' => $surgeon,
            'preop_diagnosis' => (string) ($defaults['preop_diagnosis'] ?? ''),
            'postop_diagnosis' => (string) ($defaults['postop_diagnosis'] ?? ''),
            'surgery_line' => $surgeryLine,
            'implant_type' => $implantType,
            'insertion_through' => $insertionThrough,
            'facial_nerve_monitor' => (string) ($defaults['facial_nerve_monitor'] ?? 'used'),
            'narrative_paragraphs_html' => $narrativeHtml,
            'post_op_orders' => $template['post_op_orders'],
            'time_out' => $timeOut,
            'formatting' => $formatting,
        ];
    }

    public function download(Patient $patient, MedicalRecord $record): Response
    {
        $payload = $this->buildPayload($patient, $record);
        $filename = sprintf(
            'operative-note-%s-%s.pdf',
            preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($patient->file_number ?: $patient->id)) ?: 'patient',
            now()->format('Ymd-His')
        );

        return Pdf::loadView('pdf.operation-intraop-report', $payload)
            ->setPaper('a4')
            ->download($filename);
    }

    private function lookupText(string $fieldKey, mixed $value, string $type): string
    {
        if (! filled($value)) {
            return '';
        }

        $text = OperationFieldResolver::resolve($fieldKey, $value, ['type' => $type])['text'] ?? '';

        return ($text !== '' && $text !== '—') ? (string) $text : '';
    }
}
