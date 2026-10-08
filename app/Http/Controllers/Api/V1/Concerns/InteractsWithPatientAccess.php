<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Patient;
use App\Services\PatientAccessService;

trait InteractsWithPatientAccess
{
    protected function assertPatientAccessible(Patient $patient): void
    {
        $user = request()->user();

        if (! $user) {
            abort(401);
        }

        app(PatientAccessService::class)->assertCanAccessPatient($user, $patient);
    }
}
