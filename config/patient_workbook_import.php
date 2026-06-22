<?php

return [

    'main_sheet_pattern' => '/main\s*patients/i',

    'day_sheet_pattern' => '/^day\s*(\d+)\s*$/i',

    /*
    |--------------------------------------------------------------------------
    | Header aliases → internal field keys (main registry sheet)
    |--------------------------------------------------------------------------
    */
    'main_columns' => [
        'patient_name' => ['name'],
        'approval_status' => ['approval status', 'approval_status'],
        'approval_reason' => ['reason'],
        'admission_status' => ['admission status', 'admission_status'],
        'gender' => ['gender'],
        'date_of_birth' => ['date of birth', 'date_of_birth', 'dob'],
        'current_age' => ['current age', 'age'],
        'file_number' => ['file number', 'file_number'],
        'contact_number' => ['contact number', 'contact', 'phone', 'mobile'],
        'clinical_aud' => ['c. aud', 'c aud', 'aud'],
        'clinical_speech' => ['c. speech', 'c speech', 'speech'],
        'deafness_age' => ['deafness age'],
        'audiological_test' => ['audiological test'],
        'aud_age_benefit' => ['age benefit', 'counseling'],
        'ct_findings' => ['ct'],
        'mri_findings' => ['mri'],
        'cochlear_diameter' => ['cochlear diameter'],
        'surgical_consideration' => ['surgical concideration', 'surgical consideration'],
        'surgical_side' => ['side'],
        'medical_history' => ['history', 'c. medical', 'c medical'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Day sheet columns → screening or medical record stage fields
    |--------------------------------------------------------------------------
    */
    'day_columns' => [
        'rank' => ['rank'],
        'approval_status' => ['approval status'],
        'admission_status' => ['admission status'],
        'audiology_link' => ['audiology link', 'audiology'],
        'video_link' => ['video link', 'video'],
        'patient_name' => ['name'],
        'file_number' => ['file number'],
        'patient_notes' => ['remarks', 'notes'],
        'current_age' => ['age'],
        'contact_number' => ['contact number', 'contact'],
        'anesthesia_type' => ['anasthesia', 'anesthesia'],
        'npo_time' => ['npo time', 'npo'],
        'asa_score' => ['asa score', 'asa'],
        'aud_result' => ['aud'],
        'speech_result' => ['speech'],
        'ct_findings' => ['ct'],
        'cochlear_diameter' => ['cochlear diameter'],
        'mri_findings' => ['mri'],
        'surgical_consideration' => ['surgical concideration', 'surgical consideration'],
        'medical_history' => ['history'],
        'consent' => ['consent'],
        'surgical_side' => ['side'],
        'surgeon' => ['surgeon'],
        'electrode_type' => ['electrode type'],
        'insertion_type' => ['insertion'],
        'insertion_depth_note' => ['insertion depth', 'ab'],
        'time_in_surgery' => ['time in surgery'],
        'time_out_surgery' => ['time out surgery', 'time out'],
        'intra_op_findings' => ['intra-op finding', 'intra op finding', 'intra-op finding (note)'],
        'impedance_testing' => ['impredance testing', 'impedance testing'],
        'post_op_exam' => ['post op exam', 'post-op exam'],
        'post_op_xray' => ['post-op x-ray', 'post op x-ray'],
        'swelling_size' => ['swelling size'],
        'pain_score' => ['pain score'],
        'flacc_score' => ['flacc score'],
        'ear_angle' => ['ear angle'],
        'magnet_bulge' => ['magent bulge', 'magnet bulge'],
        'redness' => ['redness'],
        'fever' => ['fever'],
        'radiology_copy' => ['radiology copy'],
        'switch_on' => ['switch on'],
        'switch_on_note' => ['switch on note'],
        'post_op_audio_education' => ['post-op audio education', 'post op audio education'],
        'post_op_speech_education' => ['post-op speech education', 'post op speech education'],
    ],

    'stage_field_map' => [
        'anesthesia' => ['anesthesia_type', 'npo_time', 'asa_score'],
        'operation' => ['surgeon', 'electrode_type', 'insertion_type', 'insertion_depth_note', 'time_in_surgery', 'time_out_surgery', 'intra_op_findings', 'impedance_testing'],
        'post_operation' => ['post_op_exam', 'post_op_xray', 'swelling_size', 'pain_score', 'flacc_score', 'ear_angle', 'magnet_bulge', 'redness', 'fever', 'radiology_copy'],
        'activation' => ['switch_on', 'switch_on_note'],
        'rehab_education' => ['post_op_audio_education', 'post_op_speech_education'],
    ],

    'screening_field_keys' => [
        'audiology_link', 'video_link', 'clinical_aud', 'clinical_speech', 'deafness_age',
        'audiological_test', 'aud_age_benefit', 'ct_findings', 'mri_findings', 'cochlear_diameter',
        'surgical_consideration', 'medical_history', 'aud_result', 'speech_result', 'consent',
    ],

];
