<?php

return [

    'disk' => env('BENEFICIARY_APPLICATIONS_DISK', 'beneficiary_applications'),

    'throttle' => [
        'max_attempts' => 5,
        'decay_minutes' => 60,
    ],

    'extra_types' => [
        8 => [
            'code' => 'cochlear_implant',
            'label' => 'beneficiary_registration.extra_types.cochlear_implant',
            'visible_steps' => ['basic', 'extra', 'health', 'attachments'],
            'extra_fields' => [
                'case_description' => [
                    'type' => 'textarea',
                    'required' => true,
                    'label' => 'beneficiary_registration.fields.case_description',
                ],
                'medical_reports' => [
                    'type' => 'files',
                    'required' => true,
                    'label' => 'beneficiary_registration.fields.medical_reports',
                    'accept' => '.pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx',
                    'max_files' => 10,
                ],
                'email' => [
                    'type' => 'email',
                    'required' => true,
                    'label' => 'beneficiary_registration.fields.email',
                ],
            ],
        ],
        9 => [
            'code' => 'other_hearing',
            'label' => 'beneficiary_registration.extra_types.other_hearing',
            'visible_steps' => ['basic', 'health', 'attachments'],
            'extra_fields' => [],
        ],
    ],

    'steps' => [
        'basic' => 'beneficiary_registration.steps.basic',
        'extra' => 'beneficiary_registration.steps.extra',
        'health' => 'beneficiary_registration.steps.health',
        'attachments' => 'beneficiary_registration.steps.attachments',
    ],

    'gender' => [
        1 => 'male',
        2 => 'female',
    ],

    'marital_status' => [
        1 => ['male' => 'أعزب', 'female' => 'عزباء'],
        2 => ['male' => 'متزوج', 'female' => 'متزوجة'],
        3 => ['male' => 'اخرى', 'female' => 'مطلقة'],
        4 => ['male' => 'الزوجة متوفية', 'female' => 'ارملة'],
        5 => ['male' => 'سجين', 'female' => 'معلقة'],
        6 => ['male' => 'متزوج بأجنبية', 'female' => 'متزوجة بأجنبي'],
    ],

    'health_status' => [
        1 => 'beneficiary_registration.options.health_healthy',
        2 => 'beneficiary_registration.options.health_sick',
    ],

    'diseases' => [
        1 => 'سكر',
        2 => 'ضغط',
        3 => 'فشل كلوي',
        4 => 'ضعف نظر',
        5 => 'ضعف سمع',
        6 => 'شلل كامل',
        7 => 'شلل نصفي',
        8 => 'إعاقة عقلية',
        9 => 'إعاقة جسدية',
        10 => 'سرطان',
        11 => 'توحد',
        12 => 'ربو',
        13 => 'التهاب كبدي',
        14 => 'انيميا منجلية',
        15 => 'انيميا البحر المتوسط',
        0 => 'اخرى',
    ],

    'disease_types' => [
        1 => 'غير مستعصي',
        2 => 'مستعصي',
    ],

    'treatment_types' => [
        1 => 'مستمر',
        2 => 'متوقف',
    ],

    'countries' => [
        'sa' => 'السعودية',
        'ae' => 'الإمارات',
        'bh' => 'البحرين',
        'kw' => 'الكويت',
        'om' => 'سلطنة عمان',
        'qa' => 'قطر',
        'ye' => 'اليمن',
        'jo' => 'الأردن',
        'eg' => 'مصر',
        'sd' => 'السودان',
        'sy' => 'سوريا',
        'iq' => 'العراق',
        'ps' => 'فلسطين',
        'lb' => 'لبنان',
        'ma' => 'المغرب',
        'dz' => 'الجزائر',
        'tn' => 'تونس',
        'ly' => 'ليبيا',
        'mr' => 'موريتانيا',
        'so' => 'الصومال',
        'dj' => 'جيبوتي',
        'km' => 'جزر القمر',
        'tr' => 'تركيا',
        'ir' => 'إيران',
        'pk' => 'باكستان',
        'af' => 'أفغانستان',
        'bd' => 'بنغلاديش',
        'in' => 'الهند',
        'id' => 'إندونيسيا',
        'my' => 'ماليزيا',
        'us' => 'الولايات المتحدة الأمريكية',
        'gb' => 'المملكة المتحدة',
        '00' => 'بدون جنسية',
        '01' => 'قبائل نازحة',
    ],

    'default_country' => 'sa',

    'name_pattern' => '/^(([ء-ي]+)\s){2,8}([ء-ي]+)$/u',

    'mobile_pattern' => '/^05\d{8}$/',

    'patient_mapping' => [
        'eligibility_status_code' => 'accepted',
        'screening_keys' => [
            'national_number' => 'beneficiary_national_number',
            'country' => 'beneficiary_country',
            'marital_status' => 'beneficiary_marital_status',
            'extra_type' => 'beneficiary_extra_type',
            'case_description' => 'beneficiary_case_description',
            'health_status' => 'beneficiary_health_status',
            'handicapped' => 'beneficiary_handicapped',
            'handicapped_percentage' => 'beneficiary_handicapped_percentage',
            'diseases' => 'beneficiary_diseases',
            'health_notes' => 'beneficiary_health_notes',
            'application_uuid' => 'beneficiary_application_uuid',
        ],
    ],

    'attachment_field_keys' => [
        'photo',
        'medical_reports',
        'health_medical_records',
        'profiles_national_images',
        'profiles_family_cards',
        'profiles_attachments',
    ],

];
