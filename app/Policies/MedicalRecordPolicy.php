<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;

class MedicalRecordPolicy
{
    public function viewAny(User $user, Patient $patient): bool
    {
        return $user->can('medical_record.view') && $user->can('view', $patient);
    }

    public function view(User $user, MedicalRecord $record): bool
    {
        return $user->can('medical_record.view') && $user->can('view', $record->patient);
    }

    public function create(User $user, Patient $patient): bool
    {
        return $user->can('medical_record.create') && $user->can('view', $patient);
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $user->can('medical_record.update') && $user->can('view', $record->patient);
    }

    public function delete(User $user, MedicalRecord $record): bool
    {
        return $user->can('medical_record.delete') && $user->can('view', $record->patient);
    }
}
