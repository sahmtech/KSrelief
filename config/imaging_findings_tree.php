<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CT / MRI hierarchical finding trees (Pre-Operative imaging)
    | Keys are stored per ear: right.ct[], right.mri[], left.ct[], left.mri[]
    |--------------------------------------------------------------------------
    */
    'ct' => [
        [
            'key' => 'normal',
            'label_key' => 'workflow.imaging_tree.ct.normal',
        ],
        [
            'key' => 'mastoid_middle_ear_with_finding',
            'label_key' => 'workflow.imaging_tree.ct.mastoid_with_finding',
            'children' => [
                ['key' => 'soft_tissue_density', 'label_key' => 'workflow.imaging_tree.ct.soft_tissue_density'],
                ['key' => 'sclerotic_mastoid', 'label_key' => 'workflow.imaging_tree.ct.sclerotic_mastoid'],
                ['key' => 'low_dura', 'label_key' => 'workflow.imaging_tree.ct.low_dura'],
                ['key' => 'tegmen_defect', 'label_key' => 'workflow.imaging_tree.ct.tegmen_defect'],
                ['key' => 'anterior_displaced_sigmoid', 'label_key' => 'workflow.imaging_tree.ct.anterior_displaced_sigmoid'],
                ['key' => 'narrow_facial_recess', 'label_key' => 'workflow.imaging_tree.ct.narrow_facial_recess'],
                ['key' => 'prominent_emissary_veins', 'label_key' => 'workflow.imaging_tree.ct.prominent_emissary_veins'],
                ['key' => 'high_riding_jugular_bulb', 'label_key' => 'workflow.imaging_tree.ct.high_riding_jugular_bulb'],
            ],
        ],
        [
            'key' => 'inner_ear_anomalies',
            'label_key' => 'workflow.imaging_tree.ct.inner_ear_anomalies',
            'children' => [
                ['key' => 'ip_1', 'label_key' => 'workflow.imaging_tree.ct.ip_1'],
                ['key' => 'ip_2', 'label_key' => 'workflow.imaging_tree.ct.ip_2'],
                ['key' => 'ip_3', 'label_key' => 'workflow.imaging_tree.ct.ip_3'],
                ['key' => 'mondini', 'label_key' => 'workflow.imaging_tree.ct.mondini'],
                ['key' => 'eva', 'label_key' => 'workflow.imaging_tree.ct.eva'],
                ['key' => 'common_cavity', 'label_key' => 'workflow.imaging_tree.ct.common_cavity'],
                ['key' => 'cochlear_hypoplasia', 'label_key' => 'workflow.imaging_tree.ct.cochlear_hypoplasia'],
                ['key' => 'cochlear_aperture_stenosis', 'label_key' => 'workflow.imaging_tree.ct.cochlear_aperture_stenosis'],
                ['key' => 'cochlear_aperture_aplasia', 'label_key' => 'workflow.imaging_tree.ct.cochlear_aperture_aplasia'],
            ],
        ],
    ],

    'mri' => [
        [
            'key' => 'normal',
            'label_key' => 'workflow.imaging_tree.mri.normal',
        ],
        [
            'key' => 'abnormal_inner_ear_iac',
            'label_key' => 'workflow.imaging_tree.mri.abnormal',
            'children' => [
                ['key' => 'hypoplastic_cvn', 'label_key' => 'workflow.imaging_tree.mri.hypoplastic_cvn'],
                ['key' => 'absent_cvn', 'label_key' => 'workflow.imaging_tree.mri.absent_cvn'],
                ['key' => 'partial_ossification', 'label_key' => 'workflow.imaging_tree.mri.partial_ossification'],
                ['key' => 'complete_ossification', 'label_key' => 'workflow.imaging_tree.mri.complete_ossification'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy DB option code → tree key path (for migrated records)
    |--------------------------------------------------------------------------
    */
    'legacy_ct_codes' => [
        'normal_inner_ear' => 'normal',
        'ip_1' => 'inner_ear_anomalies.ip_1',
        'ip_2' => 'inner_ear_anomalies.ip_2',
        'ip_3' => 'inner_ear_anomalies.ip_3',
        'mondini' => 'inner_ear_anomalies.mondini',
        'eva' => 'inner_ear_anomalies.eva',
        'common_cavity' => 'inner_ear_anomalies.common_cavity',
        'cochlear_hypoplasia' => 'inner_ear_anomalies.cochlear_hypoplasia',
    ],

    'legacy_mri_codes' => [
        'patent_cochlear' => 'normal',
        'normal_cochlear_nerve' => 'normal',
        'hypoplastic_cochlear_nerve' => 'abnormal_inner_ear_iac.hypoplastic_cvn',
        'absent_cochlear_nerve' => 'abnormal_inner_ear_iac.absent_cvn',
        'partial_ossification' => 'abnormal_inner_ear_iac.partial_ossification',
        'complete_ossification' => 'abnormal_inner_ear_iac.complete_ossification',
    ],
];
