<?php

return [
    'frequencies' => ['250', '500', '1000', '2000', '4000', '8000'],

    'types' => [
        'tympanometry' => [
            'label_key' => 'workflow.hearing_assessment.types.tympanometry',
            'layout' => 'ear_options',
            'options_key' => 'tympanometry',
        ],
        'oae' => [
            'label_key' => 'workflow.hearing_assessment.types.oae',
            'layout' => 'ear_options',
            'options_key' => 'oae',
        ],
        'boa' => [
            'label_key' => 'workflow.hearing_assessment.types.boa',
            'layout' => 'bc_ac_table',
        ],
        'vra' => [
            'label_key' => 'workflow.hearing_assessment.types.vra',
            'layout' => 'bc_ac_table',
        ],
        'cpa' => [
            'label_key' => 'workflow.hearing_assessment.types.cpa',
            'layout' => 'bc_ac_table',
        ],
        'pta' => [
            'label_key' => 'workflow.hearing_assessment.types.pta',
            'layout' => 'bc_ac_table',
        ],
        'abr' => [
            'label_key' => 'workflow.hearing_assessment.types.abr',
            'layout' => 'bc_ac_table',
            'extras' => ['cm'],
        ],
        'speech_audiometry' => [
            'label_key' => 'workflow.hearing_assessment.types.speech_audiometry',
            'layout' => 'speech_table',
        ],
        'aided_boa' => [
            'label_key' => 'workflow.hearing_assessment.types.aided_boa',
            'layout' => 'ff_table',
        ],
        'aided_vra' => [
            'label_key' => 'workflow.hearing_assessment.types.aided_vra',
            'layout' => 'ff_table',
        ],
        'aided_cpa' => [
            'label_key' => 'workflow.hearing_assessment.types.aided_cpa',
            'layout' => 'ff_table',
        ],
        'aided_pta' => [
            'label_key' => 'workflow.hearing_assessment.types.aided_pta',
            'layout' => 'ff_table',
        ],
        'aided_speech_audiometry' => [
            'label_key' => 'workflow.hearing_assessment.types.aided_speech_audiometry',
            'layout' => 'speech_table',
        ],
    ],

    'ear_options' => [
        'tympanometry' => [
            'type_a' => 'workflow.hearing_assessment.options.tympanometry.type_a',
            'type_b' => 'workflow.hearing_assessment.options.tympanometry.type_b',
            'type_c' => 'workflow.hearing_assessment.options.tympanometry.type_c',
        ],
        'oae' => [
            'present' => 'workflow.hearing_assessment.options.oae.present',
            'absent' => 'workflow.hearing_assessment.options.oae.absent',
        ],
    ],
];
