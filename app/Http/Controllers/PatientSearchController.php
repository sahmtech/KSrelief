<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('patient.view'), 403);

        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        $like = '%'.$term.'%';
        $prefix = $term.'%';

        $patients = Patient::query()
            ->with(['campaign:id,name,code', 'currentStage:id,name', 'eligibilityStatus:id,name,color'])
            ->where(function ($query) use ($like): void {
                $query->where('patient_name', 'like', $like)
                    ->orWhere('file_number', 'like', $like);
            })
            ->orderByRaw(
                'CASE
                    WHEN file_number = ? THEN 0
                    WHEN file_number LIKE ? THEN 1
                    WHEN patient_name LIKE ? THEN 2
                    ELSE 3
                END',
                [$term, $prefix, $prefix]
            )
            ->orderBy('patient_name')
            ->limit(10)
            ->get([
                'id',
                'patient_name',
                'file_number',
                'campaign_id',
                'current_stage_id',
                'eligibility_status_id',
                'date_of_birth',
                'age_years',
                'age_months',
                'gender',
                'surgery_day_number',
                'rank',
            ]);

        return response()->json([
            'results' => $patients->map(fn (Patient $patient): array => [
                'id' => $patient->id,
                'name' => $patient->patient_name,
                'file_number' => $patient->file_number,
                'campaign' => $patient->campaign?->name,
                'age' => $patient->ageLabel(),
                'gender' => $patient->gender?->label(),
                'stage' => $patient->currentStage?->name,
                'eligibility' => $patient->eligibilityStatus?->name,
                'eligibility_color' => $patient->eligibilityStatus?->color,
                'surgery_day' => $patient->surgeryDayLabel(),
                'url' => route('patients.brief', $patient),
            ]),
        ]);
    }
}
