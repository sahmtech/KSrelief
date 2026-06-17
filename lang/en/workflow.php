<?php

return [

    'title'       => 'Medical Workflow',
    'stage_history' => 'Stage History',
    'medical_records' => 'Medical Records',
    'current_stage' => 'Current Stage',
    'change_stage'  => 'Change Stage',
    'last_updated'  => 'Last Updated',
    'no_stage'      => 'No Stage Assigned',
    'no_history'    => 'No stage transitions recorded yet.',
    'no_records'    => 'No medical records found for this patient.',

    'timeline' => [
        'title'      => 'Workflow Timeline',
        'completed'  => 'Completed',
        'current'    => 'Current Stage',
        'pending'    => 'Pending',
        'changed_by' => 'By',
    ],

    'change_stage_modal' => [
        'title'       => 'Change Patient Stage',
        'new_stage'   => 'New Stage',
        'notes'       => 'Notes',
        'notes_hint'  => 'Optional notes about this stage transition.',
        'confirm'     => 'Change Stage',
        'cancel'      => 'Cancel',
    ],

    'history' => [
        'date'       => 'Date',
        'from_stage' => 'From Stage',
        'to_stage'   => 'To Stage',
        'changed_by' => 'Changed By',
        'notes'      => 'Notes',
    ],

    'records' => [
        'date'         => 'Date',
        'stage'        => 'Stage',
        'submitted_by' => 'Submitted By',
        'actions'      => 'Actions',
        'add'          => 'Add Medical Record',
        'view'         => 'View',
        'edit'         => 'Edit',
        'delete'       => 'Delete',
    ],

    'fields' => [
        // Admission
        'admission_notes'    => 'Admission Notes',
        'initial_assessment' => 'Initial Assessment',
        // Anesthesia
        'weight'           => 'Weight (kg)',
        'anesthesia_notes' => 'Anesthesia Notes',
        'readiness_status' => 'Readiness Status',
        'comments'         => 'Comments',
        // Operation
        'operation_date'  => 'Operation Date',
        'start_time'      => 'Start Time',
        'end_time'        => 'End Time',
        'surgeon'         => 'Surgeon',
        'side'            => 'Side',
        'electrode_type'  => 'Electrode Type',
        'insertion_type'  => 'Insertion Type',
        'operation_notes' => 'Operation Notes',
        // Post-Operation
        'post_op_xray'    => 'Post-Op X-Ray',
        'findings'        => 'Findings',
        'complications'   => 'Complications',
        'recommendations' => 'Recommendations',
        // Activation
        'activation_date'   => 'Activation Date',
        'activation_result' => 'Activation Result',
        // Rehab/Education
        'session_date'    => 'Session Date',
        'education_notes' => 'Education Notes',
        'rehab_plan'      => 'Rehabilitation Plan',
        'outcome'         => 'Outcome',
        'coordinator'     => 'Coordinator',
        'attending_doctor' => 'Attending Doctor',
        'specialist'      => 'Specialist',
        'admission_attachments' => 'Admission Attachments',
        'admission_attachments_hint' => 'Optional files linked to the patient record (PDF, images, documents).',
    ],

    'sides' => [
        'left'      => 'Left',
        'right'     => 'Right',
        'bilateral' => 'Bilateral',
    ],

    'messages' => [
        'stage_changed'  => 'Stage updated successfully.',
        'initial_stage'  => 'Patient entered initial workflow stage.',
        'demo_stage_transition' => 'Moved to anesthesia after admission clearance.',
        'stage_change_via_workflow' => 'Stage changes must be made from the Medical Workflow tab.',
        'no_campaign_members' => 'No campaign team members available for this role.',
        'record_created' => 'Medical record created successfully.',
        'record_updated' => 'Medical record updated successfully.',
        'record_deleted' => 'Medical record deleted successfully.',
        'confirm_delete' => 'Are you sure you want to delete this medical record? This action cannot be undone.',
    ],

    'errors' => [
        'same_stage'      => 'Cannot move patient to the same stage.',
    ],

    'validation' => [
        'stage_required'  => 'Please select a new stage.',
        'stage_not_found' => 'Selected stage does not exist.',
    ],

];
