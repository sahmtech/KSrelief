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
        'view_dossier' => 'Clinical File',
        'view_list'    => 'Records List',
        'dossier_subtitle' => 'Complete patient data grouped like the campaign Excel sheet (pre-op, intra-op, post-op).',
        'drive_links_hint' => 'Large files (CT, MRI, audiology, video) can be stored as Google Drive links instead of uploads.',
    ],

    'links' => [
        'google_drive' => 'Open in Google Drive',
        'video' => 'Open video link',
        'drive_placeholder' => 'https://drive.google.com/...',
    ],

    'phases' => [
        'screening' => 'Screening & Eligibility',
        'pre_op' => 'Pre-Operative (Before Surgery)',
        'intra_op' => 'Intra-Operative (During Surgery)',
        'post_op' => 'Post-Operative (After Surgery)',
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
        'audiology_link' => 'Audiology Link',
        'video_link' => 'Video Link',
        'clinical_aud' => 'Clinical Audiology (C. AUD)',
        'clinical_speech' => 'Clinical Speech (C. Speech)',
        'deafness_age' => 'Deafness Age',
        'audiological_test' => 'Audiological Test',
        'aud_age_benefit' => 'Age Benefit Counseling',
        'speech_video_comments' => 'Speech Video Comments',
        'speech_ci_candidate' => 'Speech CI Candidate',
        'expectations_post_ci' => 'Expectations Post CI',
        'ct_findings' => 'CT Findings',
        'mri_findings' => 'MRI Findings',
        'cochlear_diameter' => 'Cochlear Diameter',
        'surgical_consideration' => 'Surgical Consideration',
        'medical_history' => 'Medical History',
        'aud_result' => 'AUD',
        'speech_result' => 'Speech',
        'consent' => 'Consent',
        'anesthesia_type' => 'Anesthesia',
        'npo_time' => 'NPO Time',
        'asa_score' => 'ASA Score',
        'insertion_depth_note' => 'Insertion Depth',
        'time_in_surgery' => 'Time In Surgery',
        'time_out_surgery' => 'Time Out Surgery',
        'intra_op_findings' => 'Intra-Op Findings',
        'impedance_testing' => 'Impedance Testing',
        'post_op_exam' => 'Post-Op Exam',
        'swelling_size' => 'Swelling Size',
        'pain_score' => 'Pain Score (0-10)',
        'flacc_score' => 'FLACC Score',
        'ear_angle' => 'Ear Angle',
        'magnet_bulge' => 'Magnet Bulge',
        'redness' => 'Redness',
        'fever' => 'Fever',
        'radiology_copy' => 'Radiology Copy',
        'switch_on' => 'Switch On',
        'switch_on_note' => 'Switch On Note',
        'post_op_audio_education' => 'Post-Op Audio Education',
        'post_op_speech_education' => 'Post-Op Speech Education',
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
