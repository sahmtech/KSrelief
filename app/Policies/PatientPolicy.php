<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patient.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('patient.view');
    }

    public function create(User $user): bool
    {
        return $user->can('patient.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patient.update');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patient.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('patient.export');
    }

    public function importExcel(User $user): bool
    {
        return $user->can('patient.import_excel');
    }

    public function importApprove(User $user): bool
    {
        return $user->can('patient.import_approve');
    }

    public function importHistory(User $user): bool
    {
        return $user->can('patient.import_history');
    }

    public function viewWorkflow(User $user, Patient $patient): bool
    {
        return $user->can('stage.view') && $this->view($user, $patient);
    }

    public function changeStage(User $user, Patient $patient): bool
    {
        return $user->can('stage.change') && $this->view($user, $patient);
    }

    public function viewStageHistory(User $user, Patient $patient): bool
    {
        return $user->can('stage.history.view') && $this->view($user, $patient);
    }

    public function uploadAttachment(User $user, Patient $patient): bool
    {
        return $this->update($user, $patient)
            || ($this->view($user, $patient) && $user->can('medical_record.view'));
    }

    public function deleteAttachment(User $user, Patient $patient): bool
    {
        return $this->update($user, $patient);
    }
}
