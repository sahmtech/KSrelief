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
    | Stages hidden from the medical record create/edit stage dropdown (temporary)
    |--------------------------------------------------------------------------
    */
    'record_form_hidden_stage_codes' => [
        'admission',
        'activation',
        'rehab_education',
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
            'type' => 'yes_no',
            'label' => 'workflow.fields.speech_ci_candidate',
            'phase' => 'screening',
            'options_key' => 'yes_no_options',
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
            'magnet power',
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

    'follow_up_wound_options' => [
        'clean' => 'workflow.follow_up.options.wound.clean',
        'infected' => 'workflow.follow_up.options.wound.infected',
        'dehiscent' => 'workflow.follow_up.options.wound.dehiscent',
    ],

    'follow_up_implant_bed_options' => [
        'clean' => 'workflow.follow_up.options.implant_bed.clean',
        'infected' => 'workflow.follow_up.options.implant_bed.infected',
        'dehiscent' => 'workflow.follow_up.options.implant_bed.dehiscent',
    ],

    'follow_up_communication_mood_options' => [
        'verbal' => 'workflow.follow_up.options.communication_mood.verbal',
        'crying' => 'workflow.follow_up.options.communication_mood.crying',
        'pointing' => 'workflow.follow_up.options.communication_mood.pointing',
        'gesture' => 'workflow.follow_up.options.communication_mood.gesture',
        'minimal_verbal' => 'workflow.follow_up.options.communication_mood.minimal_verbal',
    ],

    'follow_up_true_word_options' => [
        'minimal_not_consistent' => 'workflow.follow_up.options.true_word.minimal_not_consistent',
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
    ],

    'follow_up_phrases_options' => [
        'minimal_not_consistent' => 'workflow.follow_up.options.phrases.minimal_not_consistent',
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
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

    'yes_no_options' => [
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
    ],

    'medical_history_pre_op_request_options' => [
        'farther_investigation' => 'workflow.fields.medical_history_options.farther_investigation',
        'need_ct_tb' => 'workflow.fields.medical_history_options.need_ct_tb',
        'need_x_ray' => 'workflow.fields.medical_history_options.need_x_ray',
        'need_rmi' => 'workflow.fields.medical_history_options.need_rmi',
    ],

    'medical_history_general_condition_options' => [
        'medically_free' => 'workflow.fields.medical_history_options.medically_free',
        'need_clearance' => 'workflow.fields.medical_history_options.need_clearance',
    ],

    'pre_op_clinical_decision_options' => [
        'accepted' => 'workflow.pre_op.options.clinical_decision.accepted',
        'rejected' => 'workflow.pre_op.options.clinical_decision.rejected',
        'postponed_further_test' => 'workflow.pre_op.options.clinical_decision.postponed_further_test',
    ],

    'pre_op_audiology_status_options' => [
        'complete' => 'workflow.pre_op.options.audiology_status.complete',
        'required_more_assessment' => 'workflow.pre_op.options.audiology_status.required_more_assessment',
        'not_done' => 'workflow.pre_op.options.audiology_status.not_done',
    ],

    'pre_op_audiology_decision_options' => [
        'accepted_need_more_test' => 'workflow.pre_op.options.audiology_decision.accepted_need_more_test',
        'postponed_further_exam' => 'workflow.pre_op.options.audiology_decision.postponed_further_exam',
        'rejected' => 'workflow.pre_op.options.audiology_decision.rejected',
        'accepted_left' => 'workflow.pre_op.options.audiology_decision.accepted_left',
        'accepted_right' => 'workflow.pre_op.options.audiology_decision.accepted_right',
    ],

    'pre_op_speech_communication_mood_options' => [
        'verbal' => 'workflow.follow_up.options.communication_mood.verbal',
        'crying' => 'workflow.follow_up.options.communication_mood.crying',
        'pointing' => 'workflow.follow_up.options.communication_mood.pointing',
        'gesture' => 'workflow.follow_up.options.communication_mood.gesture',
        'minimal_verbal' => 'workflow.follow_up.options.communication_mood.minimal_verbal',
    ],

    'pre_op_speech_iq_options' => [
        'average' => 'workflow.pre_op.options.speech_iq.average',
        'below_average' => 'workflow.pre_op.options.speech_iq.below_average',
    ],

    'pre_op_speech_cognitive_function_options' => [
        'wnl' => 'workflow.pre_op.options.cognitive_function.wnl',
        'deficit' => 'workflow.pre_op.options.cognitive_function.deficit',
    ],

    'pre_op_speech_true_word_options' => [
        'minimal_not_consistent' => 'workflow.follow_up.options.true_word.minimal_not_consistent',
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
    ],

    'pre_op_speech_phrases_options' => [
        'minimal_not_consistent' => 'workflow.follow_up.options.phrases.minimal_not_consistent',
        'yes' => 'workflow.fields.yes_no_options.yes',
        'no' => 'workflow.fields.yes_no_options.no',
    ],

    'pre_op_speech_assessment_options' => [
        'complete' => 'workflow.pre_op.options.speech_assessment.complete',
        'required_more_assessment' => 'workflow.pre_op.options.speech_assessment.required_more_assessment',
        'not_done_yet' => 'workflow.pre_op.options.speech_assessment.not_done_yet',
    ],

    'pre_op_speech_decision_options' => [
        'accepted' => 'workflow.pre_op.options.speech_decision.accepted',
        'accepted_need_more_test' => 'workflow.pre_op.options.speech_decision.accepted_need_more_test',
        'postponed_further_exam' => 'workflow.pre_op.options.speech_decision.postponed_further_exam',
        'rejected' => 'workflow.pre_op.options.speech_decision.rejected',
        'very_poor_prognosis' => 'workflow.pre_op.options.speech_decision.very_poor_prognosis',
    ],

    'operation_insertion_depth_options' => [
        'full_insertion' => 'workflow.operation.options.insertion_depth.full_insertion',
        'partial_insertion' => 'workflow.operation.options.insertion_depth.partial_insertion',
    ],

    'operation_intra_op_findings_options' => [
        'uneventful' => 'workflow.operation.options.intra_op_findings.uneventful',
        'difficult_insertion' => 'workflow.operation.options.intra_op_findings.difficult_insertion',
        'csf_gusher' => 'workflow.operation.options.intra_op_findings.csf_gusher',
    ],

    'post_op_wound_options' => [
        'clean' => 'workflow.post_op.options.wound.clean',
        'swelling' => 'workflow.post_op.options.wound.swelling',
        'hematoma' => 'workflow.post_op.options.wound.hematoma',
    ],

    'post_op_implant_bed_options' => [
        'clean' => 'workflow.post_op.options.implant_bed.clean',
        'swelling' => 'workflow.post_op.options.implant_bed.swelling',
        'hematoma' => 'workflow.post_op.options.implant_bed.hematoma',
    ],

    'post_op_facial_nerve_options' => [
        'intact' => 'workflow.post_op.options.facial_nerve.intact',
        'partial_weakness' => 'workflow.post_op.options.facial_nerve.partial_weakness',
        'complete_weakness' => 'workflow.post_op.options.facial_nerve.complete_weakness',
    ],

    'post_op_xray_options' => [
        'full_insertion' => 'workflow.post_op.options.post_op_xray.full_insertion',
        'partial_insertion' => 'workflow.post_op.options.post_op_xray.partial_insertion',
    ],

    'operation_audio_test_keys' => [
        'Impedance',
        'E cap',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre Operation medical record stage (legacy key list for brief/import)
    |--------------------------------------------------------------------------
    */
    'pre_operation_field_keys' => [
        'physician_assessment',
        'imaging_findings',
        'audiology_decision',
        'speech_assessment',
    ],

    /*
    |--------------------------------------------------------------------------
    | Workflow stage fields (medical_records.fields_json)
    |--------------------------------------------------------------------------
    */
    'stage_fields' => [
        'pre_operation' => [
            'physician_assessment' => [
                'type' => 'pre_op_physician_assessment',
                'phase' => 'pre_op',
                'label' => 'workflow.pre_op.cards.physician_assessment',
            ],
            'imaging_findings' => [
                'type' => 'imaging_findings',
                'label' => 'workflow.fields.imaging_findings',
                'phase' => 'pre_op',
                'allow_add_options' => true,
            ],
            'audiology_decision' => [
                'type' => 'pre_op_audiology_decision',
                'phase' => 'pre_op',
                'label' => 'workflow.pre_op.cards.audiology_decision',
            ],
            'speech_assessment' => [
                'type' => 'pre_op_speech_assessment',
                'phase' => 'pre_op',
                'label' => 'workflow.pre_op.cards.speech_assessment',
            ],
        ],
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
        ],
        'operation' => [
            'operation_date' => ['type' => 'date', 'phase' => 'intra_op', 'label' => 'workflow.fields.operation_date', 'required' => true],
            'surgeon' => ['type' => 'member_select', 'member_role' => 'doctor', 'phase' => 'intra_op', 'label' => 'workflow.fields.surgeon', 'required' => true],
            'implant_company_id' => ['type' => 'company_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.implant_company', 'required' => true],
            'electrode_type_id' => ['type' => 'electrode_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.electrode_type', 'depends_on' => 'implant_company_id', 'required' => true],
            'insertion_approach_id' => ['type' => 'insertion_approach_select', 'phase' => 'intra_op', 'label' => 'workflow.fields.insertion_approach'],
            'insertion_depth' => ['type' => 'operation_insertion_depth', 'phase' => 'intra_op', 'label' => 'workflow.operation.fields.insertion_depth'],
            'time_in_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_in_surgery'],
            'time_out_surgery' => ['type' => 'time', 'phase' => 'intra_op', 'label' => 'workflow.fields.time_out_surgery'],
            'audio_test' => ['type' => 'operation_audio_test', 'phase' => 'intra_op', 'label' => 'workflow.operation.fields.audio_test'],
            'intra_op_findings' => ['type' => 'operation_intra_op_findings', 'phase' => 'intra_op', 'label' => 'workflow.fields.intra_op_findings'],
            'operation_notes' => ['type' => 'textarea', 'phase' => 'intra_op', 'label' => 'workflow.fields.operation_notes'],
        ],
        'follow_up' => [
            'clinical_assessment' => [
                'type' => 'follow_up_clinical_assessment',
                'phase' => 'follow_up',
                'label' => 'workflow.follow_up.cards.clinical_assessment',
            ],
            'audiology_assessment' => [
                'type' => 'follow_up_audiology_assessment',
                'phase' => 'follow_up',
                'label' => 'workflow.follow_up.cards.audiology_assessment',
            ],
            'speech_assessment' => [
                'type' => 'follow_up_speech_assessment',
                'phase' => 'follow_up',
                'label' => 'workflow.follow_up.cards.speech_assessment',
            ],
            'follow_up_notes' => [
                'type' => 'follow_up_notes',
                'phase' => 'follow_up',
                'label' => 'workflow.follow_up.cards.notes',
            ],
        ],
        'post_operation' => [
            'physician_assessment' => [
                'type' => 'post_op_physician_assessment',
                'phase' => 'post_op',
                'label' => 'workflow.post_op.cards.physician_assessment',
            ],
            'clinical_aud' => [
                'type' => 'post_op_clinical_aud',
                'phase' => 'post_op',
                'label' => 'workflow.post_op.cards.clinical_aud',
            ],
            'counselling' => [
                'type' => 'yes_no',
                'phase' => 'post_op',
                'label' => 'workflow.post_op.fields.counselling',
            ],
            'post_op_notes' => [
                'type' => 'post_op_notes',
                'phase' => 'post_op',
                'label' => 'workflow.post_op.cards.notes',
            ],
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
