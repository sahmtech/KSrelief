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
        'follow_up' => [
            'label' => 'workflow.phases.follow_up',
            'color' => '#8B5CF6',
            'background' => '#EDE9FE',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Main registry / screening fields (patient.screening_data)
    |--------------------------------------------------------------------------
    */
    'screening_fields' => [
        'clinical_aud' => [
            'type' => 'clinical_aud',
            'label' => 'workflow.fields.clinical_aud',
            'phase' => 'screening',
            'metrics_profile' => 'screening',
            'with_status' => true,
            'allow_add_metrics' => true,
        ],
        'clinical_speech' => [
            'type' => 'clinical_speech',
            'label' => 'workflow.fields.clinical_speech',
            'phase' => 'screening',
            'variant' => 'screening',
        ],
        'deafness_age' => ['type' => 'text', 'label' => 'workflow.fields.deafness_age', 'phase' => 'screening'],
        'speech_ci_candidate' => [
            'type' => 'select',
            'label' => 'workflow.fields.speech_ci_candidate',
            'phase' => 'screening',
            'options_key' => 'speech_ci_candidate_options',
        ],
        'expectations_post_ci' => [
            'type' => 'expandable_checklist',
            'label' => 'workflow.fields.expectations_post_ci',
            'phase' => 'screening',
            'settings_options' => 'expectation_post_ci',
            'allow_add_options' => false,
        ],
        'imaging_findings' => [
            'type' => 'imaging_findings',
            'label' => 'workflow.fields.imaging_findings',
            'phase' => 'pre_op',
        ],
        'cochlear_diameter' => ['type' => 'url', 'label' => 'workflow.fields.cochlear_diameter', 'phase' => 'pre_op'],
        'surgical_consideration' => ['type' => 'textarea', 'label' => 'workflow.fields.surgical_consideration', 'phase' => 'pre_op'],
        'medical_history' => [
            'type' => 'medical_history_screening',
            'label' => 'workflow.fields.medical_history',
            'phase' => 'pre_op',
        ],
        'aud_result' => ['type' => 'select', 'label' => 'workflow.fields.aud_result', 'phase' => 'pre_op', 'options' => ['0' => '0', '1' => '1']],
        'speech_result' => ['type' => 'select', 'label' => 'workflow.fields.speech_result', 'phase' => 'pre_op', 'options' => ['0' => '0', '1' => '1']],
        'consent' => ['type' => 'text', 'label' => 'workflow.fields.consent', 'phase' => 'pre_op'],
        'audiology_link' => ['type' => 'url', 'label' => 'workflow.fields.audiology_link', 'phase' => 'pre_op'],
        'video_link' => ['type' => 'url', 'label' => 'workflow.fields.video_link', 'phase' => 'pre_op'],
    ],

    'clinical_aud_profiles' => [
        'screening' => [
            'Hearing level',
        ],
        'post_operation' => [
            'Impedance',
            'E cap',
        ],
        'follow_up' => [
            'Impedance',
            'E cap',
            'Aided Hearing level',
            'Data logging',
            'Hearing age',
            'Magnet',
        ],
    ],

    'clinical_speech_follow_up_keys' => [
        'Cap',
        'SIR',
    ],

    'clinical_aud_status_options' => [
        'complete' => 'workflow.fields.clinical_aud_status_options.complete',
        'required_more' => 'workflow.fields.clinical_aud_status_options.required_more',
        'assessment' => 'workflow.fields.clinical_aud_status_options.assessment',
        'not_done' => 'workflow.fields.clinical_aud_status_options.not_done',
    ],

    'clinical_speech_assessment_options' => [
        'communication_mood' => 'workflow.fields.clinical_speech_assessment_options.communication_mood',
        'true_word' => 'workflow.fields.clinical_speech_assessment_options.true_word',
        'phrases' => 'workflow.fields.clinical_speech_assessment_options.phrases',
    ],

    'clinical_speech_screening_assessment_options' => [
        'updated' => 'workflow.fields.clinical_speech_screening_assessment_options.updated',
        'need_assessment' => 'workflow.fields.clinical_speech_screening_assessment_options.need_assessment',
        'poor_outcome' => 'workflow.fields.clinical_speech_screening_assessment_options.poor_outcome',
    ],

    'speech_ci_candidate_options' => [
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
    ],

    'medical_history_pre_op_request_options' => [
        'farther_investigation' => 'workflow.fields.medical_history_options.farther_investigation',
        'need_ct_tb' => 'workflow.fields.medical_history_options.need_ct_tb',
        'need_x_ray' => 'workflow.fields.medical_history_options.need_x_ray',
    ],

    'medical_history_general_condition_options' => [
        'medically_free' => 'workflow.fields.medical_history_options.medically_free',
        'need_clearance' => 'workflow.fields.medical_history_options.need_clearance',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre Operation medical record stage (mirrors screening_fields order)
    |--------------------------------------------------------------------------
    */
    'pre_operation_field_keys' => [
        'clinical_aud',
        'clinical_speech',
        'deafness_age',
        'speech_ci_candidate',
        'expectations_post_ci',
        'imaging_findings',
        'cochlear_diameter',
        'surgical_consideration',
        'medical_history',
        'aud_result',
        'speech_result',
        'consent',
        'audiology_link',
        'video_link',
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow stage fields (medical_records.fields_json)
    |--------------------------------------------------------------------------
    */
    'stage_fields' => [
        'pre_operation' => [],
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
            'implant_company_id' => ['type' => 'company_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.implant_company'],
            'electrode_type_id' => ['type' => 'electrode_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.electrode_type', 'depends_on' => 'implant_company_id'],
            'insertion_approach_id' => ['type' => 'insertion_approach_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.insertion_approach'],
            'insertion_depth_note' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.insertion_depth_note'],
            'time_in_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_in_surgery'],
            'time_out_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_out_surgery'],
            'intra_op_findings' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.intra_op_findings'],
            'impedance_testing' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.impedance_testing'],
            'operation_notes' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.operation_notes'],
        ],
        'follow_up' => [
            'clinical_aud' => [
                'type' => 'clinical_aud',
                'phase' => 'follow_up',
                'label' => 'workflow.fields.clinical_aud',
                'metrics_profile' => 'follow_up',
                'with_status' => false,
                'allow_add_metrics' => true,
            ],
            'clinical_speech' => [
                'type' => 'clinical_speech_followup',
                'phase' => 'follow_up',
                'label' => 'workflow.fields.clinical_speech',
                'allow_add_metrics' => true,
            ],
            'follow_up_notes' => ['type' => 'textarea', 'phase' => 'follow_up', 'label' => 'workflow.fields.follow_up_notes'],
        ],
        'post_operation' => [
            'clinical_aud' => [
                'type' => 'clinical_aud',
                'phase' => 'post_op',
                'label' => 'workflow.fields.clinical_aud',
                'metrics_profile' => 'post_operation',
                'with_status' => false,
                'allow_add_metrics' => true,
            ],
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
