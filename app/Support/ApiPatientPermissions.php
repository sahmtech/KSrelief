<?php

namespace App\Support;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;

final class ApiPatientPermissions
{
    /**
     * @return array<string, bool>
     */
    public static function for(User $user, Patient $patient): array
    {
        return [
            'can_update' => $user->can('update', $patient),
            'can_delete' => $user->can('delete', $patient),
            'can_view_workflow' => $user->can('viewWorkflow', $patient),
            'can_change_stage' => $user->can('changeStage', $patient),
            'can_view_stage_history' => $user->can('viewStageHistory', $patient),
            'can_view_records' => $user->can('viewAny', [MedicalRecord::class, $patient]),
            'can_create_record' => $user->can('create', [MedicalRecord::class, $patient]),
            'can_upload_attachment' => $user->can('uploadAttachment', $patient),
            'can_delete_attachment' => $user->can('deleteAttachment', $patient),
            'can_view_activities' => $user->can('activity.view'),
            'can_view_transportation' => $user->can('transportation.view'),
        ];
    }
}
