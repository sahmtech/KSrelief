<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clinical phases (matches client Excel color groups)
    |--------------------------------------------------------------------------
    */
    'phases' => [
        'pre_op' => [
            'label' => 'workflow.phases.pre_op',
            'color' => '#FFD966',
            'background' => '#FFF2CC',
        ],
        'intra_op' => [
            'label' => 'workflow.phases.intra_op',
            'color' => '#6AA84F',
            'background' => '#B6D7A8',
        ],
        'post_op' => [
            'label' => 'workflow.phases.post_op',
            'color' => '#3C78D8',
            'background' => '#9FC5E8',
        ],
        'screening' => [
            'label' => 'workflow.phases.screening',
            'color' => '#356854',
            'background' => '#D9EAD3',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Main registry / screening fields (patient.screening_data)
    |--------------------------------------------------------------------------
    */
    'screening_fields' => [
        'audiology_link' => ['type' => 'url', 'label' => 'workflow.fields.audiology_link', 'phase' => 'pre_op'],
        'video_link' => ['type' => 'url', 'label' => 'workflow.fields.video_link', 'phase' => 'pre_op'],
        'clinical_aud' => ['type' => 'textarea', 'label' => 'workflow.fields.clinical_aud', 'phase' => 'screening'],
        'clinical_speech' => ['type' => 'textarea', 'label' => 'workflow.fields.clinical_speech', 'phase' => 'screening'],
        'deafness_age' => ['type' => 'text', 'label' => 'workflow.fields.deafness_age', 'phase' => 'screening'],
        'audiological_test' => ['type' => 'url', 'label' => 'workflow.fields.audiological_test', 'phase' => 'screening'],
        'aud_age_benefit' => ['type' => 'text', 'label' => 'workflow.fields.aud_age_benefit', 'phase' => 'screening'],
        'speech_video_comments' => ['type' => 'textarea', 'label' => 'workflow.fields.speech_video_comments', 'phase' => 'screening'],
        'speech_ci_candidate' => ['type' => 'text', 'label' => 'workflow.fields.speech_ci_candidate', 'phase' => 'screening'],
        'expectations_post_ci' => ['type' => 'textarea', 'label' => 'workflow.fields.expectations_post_ci', 'phase' => 'screening'],
        'ct_findings' => ['type' => 'textarea', 'label' => 'workflow.fields.ct_findings', 'phase' => 'pre_op'],
        'mri_findings' => ['type' => 'textarea', 'label' => 'workflow.fields.mri_findings', 'phase' => 'pre_op'],
        'cochlear_diameter' => ['type' => 'url', 'label' => 'workflow.fields.cochlear_diameter', 'phase' => 'pre_op'],
        'surgical_consideration' => ['type' => 'textarea', 'label' => 'workflow.fields.surgical_consideration', 'phase' => 'pre_op'],
        'medical_history' => ['type' => 'textarea', 'label' => 'workflow.fields.medical_history', 'phase' => 'pre_op'],
        'aud_result' => ['type' => 'select', 'label' => 'workflow.fields.aud_result', 'phase' => 'pre_op', 'options' => ['0' => '0', '1' => '1']],
        'speech_result' => ['type' => 'select', 'label' => 'workflow.fields.speech_result', 'phase' => 'pre_op', 'options' => ['0' => '0', '1' => '1']],
        'consent' => ['type' => 'text', 'label' => 'workflow.fields.consent', 'phase' => 'pre_op'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow stage fields (medical_records.fields_json)
    |--------------------------------------------------------------------------
    */
    'stage_fields' => [
        'admission' => [
            'coordinator' => ['type' => 'member_select', 'member_role' => 'coordinator', 'phase' => 'pre_op', 'label' => 'workflow.fields.coordinator'],
            'admission_notes' => ['type' => 'textarea', 'phase' => 'pre_op', 'label' => 'workflow.fields.admission_notes'],
            'initial_assessment' => ['type' => 'textarea', 'phase' => 'pre_op', 'label' => 'workflow.fields.initial_assessment'],
        ],
        'anesthesia' => [
            'attending_doctor' => ['type' => 'member_select', 'member_role' => 'doctor', 'phase' => 'pre_op', 'label' => 'workflow.fields.attending_doctor'],
            'anesthesia_type' => ['type' => 'text', 'phase' => 'pre_op', 'label' => 'workflow.fields.anesthesia_type'],
            'npo_time' => ['type' => 'text', 'phase' => 'pre_op', 'label' => 'workflow.fields.npo_time'],
            'asa_score' => ['type' => 'text', 'phase' => 'pre_op', 'label' => 'workflow.fields.asa_score'],
            'weight' => ['type' => 'number', 'phase' => 'pre_op', 'label' => 'workflow.fields.weight'],
            'anesthesia_notes' => ['type' => 'textarea', 'phase' => 'pre_op', 'label' => 'workflow.fields.anesthesia_notes'],
            'readiness_status' => ['type' => 'text', 'phase' => 'pre_op', 'label' => 'workflow.fields.readiness_status'],
            'comments' => ['type' => 'textarea', 'phase' => 'pre_op', 'label' => 'workflow.fields.comments'],
        ],
        'operation' => [
            'operation_date' => ['type' => 'date', 'phase' => 'intra_op', 'label' => 'workflow.fields.operation_date'],
            'surgeon' => ['type' => 'member_select', 'member_role' => 'doctor', 'phase' => 'intra_op', 'label' => 'workflow.fields.surgeon'],
            'specialist' => ['type' => 'member_select', 'member_role' => 'specialist', 'phase' => 'intra_op', 'label' => 'workflow.fields.specialist'],
            'electrode_type' => ['type' => 'text', 'phase' => 'intra_op', 'label' => 'workflow.fields.electrode_type'],
            'insertion_type' => ['type' => 'text', 'phase' => 'intra_op', 'label' => 'workflow.fields.insertion_type'],
            'insertion_depth_note' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.insertion_depth_note'],
            'time_in_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_in_surgery'],
            'time_out_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_out_surgery'],
            'intra_op_findings' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.intra_op_findings'],
            'impedance_testing' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.impedance_testing'],
            'operation_notes' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.operation_notes'],
        ],
        'post_operation' => [
            'post_op_exam' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.post_op_exam'],
            'post_op_xray' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.post_op_xray'],
            'swelling_size' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.swelling_size'],
            'pain_score' => ['type' => 'number', 'phase' => 'post_op', 'label' => 'workflow.fields.pain_score'],
            'flacc_score' => ['type' => 'number', 'phase' => 'post_op', 'label' => 'workflow.fields.flacc_score'],
            'ear_angle' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.ear_angle'],
            'magnet_bulge' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.magnet_bulge'],
            'redness' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.redness'],
            'fever' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.fever'],
            'radiology_copy' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.radiology_copy'],
            'findings' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.findings'],
            'complications' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.complications'],
            'recommendations' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.recommendations'],
        ],
        'activation' => [
            'coordinator' => ['type' => 'member_select', 'member_role' => 'coordinator', 'phase' => 'post_op', 'label' => 'workflow.fields.coordinator'],
            'activation_date' => ['type' => 'date', 'phase' => 'post_op', 'label' => 'workflow.fields.activation_date'],
            'switch_on' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.switch_on'],
            'switch_on_note' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.switch_on_note'],
            'activation_result' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.activation_result'],
            'comments' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.comments'],
        ],
        'rehab_education' => [
            'specialist' => ['type' => 'member_select', 'member_role' => 'specialist', 'phase' => 'post_op', 'label' => 'workflow.fields.specialist'],
            'session_date' => ['type' => 'date', 'phase' => 'post_op', 'label' => 'workflow.fields.session_date'],
            'post_op_audio_education' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.post_op_audio_education'],
            'post_op_speech_education' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.post_op_speech_education'],
            'education_notes' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.education_notes'],
            'rehab_plan' => ['type' => 'textarea', 'phase' => 'post_op', 'label' => 'workflow.fields.rehab_plan'],
            'outcome' => ['type' => 'text', 'phase' => 'post_op', 'label' => 'workflow.fields.outcome'],
        ],
    ],

];
