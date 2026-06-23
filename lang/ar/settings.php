<?php

return [
    'dashboard' => [
        'title' => 'الإعدادات الرئيسية',
        'subtitle' => 'إدارة البيانات المرجعية وقيم البحث في النظام',
        'total' => 'الإجمالي',
        'active' => 'نشط',
        'quick_access' => 'إدارة',
        'cards' => [
            'countries' => 'الدول',
            'cities' => 'المدن',
            'specialties' => 'التخصصات',
            'member_roles' => 'أدوار الأعضاء',
            'patient_eligibility_statuses' => 'حالات أهلية المرضى',
            'patient_stages' => 'مراحل المرضى',
            'activity_types' => 'أنواع الأنشطة',
            'transportation_locations' => 'مواقع النقل',
            'attendance_statuses' => 'حالات الحضور',
            'campaign_statuses' => 'حالات الحملات',
            'implant_companies' => 'شركات الزرع',
            'insertion_approaches' => 'طرق الإدخال',
            'ct_finding_options' => 'خيارات نتائج CT',
            'mri_finding_options' => 'خيارات نتائج MRI',
            'expectation_post_ci_options' => 'خيارات التوقعات بعد الزراعة',
        ],
    ],

    'debug' => [
        'title' => 'أدوات التطوير',
        'backfill_description' => 'تظهر فقط عند APP_DEBUG=true. تولّد أكواد الحملات والمرضى الناقصة للسجلات المخزنة.',
        'missing_campaign_codes' => 'حملات بدون كود',
        'missing_patient_codes' => 'مرضى بدون كود',
        'backfill_action' => 'توليد الأكواد الناقصة',
        'backfill_confirm' => 'توليد الأكواد لكل الحملات والمرضى الذين لا يملكون كوداً؟',
        'backfill_success' => 'تم توليد :campaigns كود حملة و :patients كود مريض.',
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    'fields' => [
        'name' => 'الاسم',
        'name_ar' => 'الاسم بالعربية',
        'code' => 'الرمز',
        'iso2' => 'ISO2',
        'iso3' => 'ISO3',
        'phone_code' => 'رمز الهاتف',
        'country' => 'الدولة',
        'description' => 'الوصف',
        'color' => 'اللون',
        'sort_order' => 'ترتيب العرض',
        'is_default' => 'افتراضي',
        'type' => 'النوع',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'created_by' => 'أنشئ بواسطة',
        'updated_by' => 'حدّث بواسطة',
    ],

    'filters' => [
        'title' => 'التصفية',
        'search' => 'بحث',
        'search_placeholder' => 'ابحث بالاسم أو الرمز...',
        'status' => 'الحالة',
        'country' => 'الدولة',
        'type' => 'النوع',
        'all_statuses' => 'جميع الحالات',
        'all_countries' => 'جميع الدول',
        'all_types' => 'جميع الأنواع',
        'apply' => 'تطبيق التصفية',
        'reset' => 'إعادة تعيين',
    ],

    'table' => [
        'name' => 'الاسم',
        'code' => 'الرمز',
        'country' => 'الدولة',
        'description' => 'الوصف',
        'color' => 'اللون',
        'sort_order' => 'الترتيب',
        'is_default' => 'افتراضي',
        'type' => 'النوع',
        'status' => 'الحالة',
        'iso2' => 'ISO2',
        'iso3' => 'ISO3',
        'phone_code' => 'رمز الهاتف',
        'actions' => 'الإجراءات',
    ],

    'actions' => [
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'create' => 'إنشاء',
    ],

    'messages' => [
        'empty' => 'لا توجد سجلات.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا السجل؟',
        'yes' => 'نعم',
        'no' => 'لا',
    ],

    'sections' => [
        'details' => 'التفاصيل',
        'audit' => 'معلومات التدقيق',
    ],

    'transportation_types' => [
        'hotel' => 'فندق',
        'hospital' => 'مستشفى',
        'airport' => 'مطار',
        'other' => 'أخرى',
    ],

    'entities' => [
        'countries' => [
            'messages' => [
                'created' => 'تم إنشاء الدولة بنجاح.',
                'updated' => 'تم تحديث الدولة بنجاح.',
                'deleted' => 'تم حذف الدولة بنجاح.',
            ],
        ],
        'cities' => [
            'messages' => [
                'created' => 'تم إنشاء المدينة بنجاح.',
                'updated' => 'تم تحديث المدينة بنجاح.',
                'deleted' => 'تم حذف المدينة بنجاح.',
            ],
        ],
        'specialties' => [
            'messages' => [
                'created' => 'تم إنشاء التخصص بنجاح.',
                'updated' => 'تم تحديث التخصص بنجاح.',
                'deleted' => 'تم حذف التخصص بنجاح.',
            ],
        ],
        'member_roles' => [
            'messages' => [
                'created' => 'تم إنشاء دور العضو بنجاح.',
                'updated' => 'تم تحديث دور العضو بنجاح.',
                'deleted' => 'تم حذف دور العضو بنجاح.',
            ],
        ],
        'patient_eligibility_statuses' => [
            'messages' => [
                'created' => 'تم إنشاء حالة أهلية المريض بنجاح.',
                'updated' => 'تم تحديث حالة أهلية المريض بنجاح.',
                'deleted' => 'تم حذف حالة أهلية المريض بنجاح.',
            ],
        ],
        'patient_stages' => [
            'messages' => [
                'created' => 'تم إنشاء مرحلة المريض بنجاح.',
                'updated' => 'تم تحديث مرحلة المريض بنجاح.',
                'deleted' => 'تم حذف مرحلة المريض بنجاح.',
            ],
        ],
        'activity_types' => [
            'messages' => [
                'created' => 'تم إنشاء نوع النشاط بنجاح.',
                'updated' => 'تم تحديث نوع النشاط بنجاح.',
                'deleted' => 'تم حذف نوع النشاط بنجاح.',
            ],
        ],
        'transportation_locations' => [
            'messages' => [
                'created' => 'تم إنشاء موقع النقل بنجاح.',
                'updated' => 'تم تحديث موقع النقل بنجاح.',
                'deleted' => 'تم حذف موقع النقل بنجاح.',
            ],
        ],
        'attendance_statuses' => [
            'messages' => [
                'created' => 'تم إنشاء حالة الحضور بنجاح.',
                'updated' => 'تم تحديث حالة الحضور بنجاح.',
                'deleted' => 'تم حذف حالة الحضور بنجاح.',
            ],
        ],
        'campaign_statuses' => [
            'messages' => [
                'created' => 'تم إنشاء حالة الحملة بنجاح.',
                'updated' => 'تم تحديث حالة الحملة بنجاح.',
                'deleted' => 'تم حذف حالة الحملة بنجاح.',
            ],
        ],
        'implant_companies' => [
            'messages' => [
                'created' => 'تم إنشاء شركة الزرع بنجاح.',
                'updated' => 'تم تحديث شركة الزرع بنجاح.',
                'deleted' => 'تم حذف شركة الزرع بنجاح.',
            ],
        ],
        'insertion_approaches' => [
            'messages' => [
                'created' => 'تم إنشاء طريقة الإدخال بنجاح.',
                'updated' => 'تم تحديث طريقة الإدخال بنجاح.',
                'deleted' => 'تم حذف طريقة الإدخال بنجاح.',
            ],
        ],
        'ct_finding_options' => [
            'messages' => [
                'created' => 'تم إنشاء خيار نتيجة CT بنجاح.',
                'updated' => 'تم تحديث خيار نتيجة CT بنجاح.',
                'deleted' => 'تم حذف خيار نتيجة CT بنجاح.',
            ],
        ],
        'mri_finding_options' => [
            'messages' => [
                'created' => 'تم إنشاء خيار نتيجة MRI بنجاح.',
                'updated' => 'تم تحديث خيار نتيجة MRI بنجاح.',
                'deleted' => 'تم حذف خيار نتيجة MRI بنجاح.',
            ],
        ],
        'expectation_post_ci_options' => [
            'messages' => [
                'created' => 'تم إنشاء خيار التوقعات بعد الزراعة بنجاح.',
                'updated' => 'تم تحديث خيار التوقعات بعد الزراعة بنجاح.',
                'deleted' => 'تم حذف خيار التوقعات بعد الزراعة بنجاح.',
            ],
        ],
    ],

    'countries' => [
        'title' => 'الدول',
        'subtitle' => 'إدارة بيانات الدول المرجعية',
        'singular' => 'دولة',
        'add' => 'إضافة دولة',
        'create_title' => 'إنشاء دولة',
        'create_subtitle' => 'إضافة سجل دولة جديد',
        'edit_title' => 'تعديل دولة',
        'edit_subtitle' => 'تحديث معلومات الدولة',
        'show_title' => 'تفاصيل الدولة',
        'show_subtitle' => 'عرض معلومات الدولة',
    ],

    'cities' => [
        'title' => 'المدن',
        'subtitle' => 'إدارة بيانات المدن المرجعية',
        'singular' => 'مدينة',
        'add' => 'إضافة مدينة',
        'create_title' => 'إنشاء مدينة',
        'create_subtitle' => 'إضافة سجل مدينة جديد',
        'edit_title' => 'تعديل مدينة',
        'edit_subtitle' => 'تحديث معلومات المدينة',
        'show_title' => 'تفاصيل المدينة',
        'show_subtitle' => 'عرض معلومات المدينة',
    ],

    'specialties' => [
        'title' => 'التخصصات',
        'subtitle' => 'إدارة بيانات التخصصات الطبية المرجعية',
        'singular' => 'تخصص',
        'add' => 'إضافة تخصص',
        'create_title' => 'إنشاء تخصص',
        'create_subtitle' => 'إضافة سجل تخصص جديد',
        'edit_title' => 'تعديل تخصص',
        'edit_subtitle' => 'تحديث معلومات التخصص',
        'show_title' => 'تفاصيل التخصص',
        'show_subtitle' => 'عرض معلومات التخصص',
    ],

    'member_roles' => [
        'title' => 'أدوار الأعضاء',
        'subtitle' => 'إدارة بيانات أدوار الطاقم الطبي المرجعية',
        'singular' => 'دور عضو',
        'add' => 'إضافة دور',
        'create_title' => 'إنشاء دور عضو',
        'create_subtitle' => 'إضافة سجل دور عضو جديد',
        'edit_title' => 'تعديل دور عضو',
        'edit_subtitle' => 'تحديث معلومات دور العضو',
        'show_title' => 'تفاصيل دور العضو',
        'show_subtitle' => 'عرض معلومات دور العضو',
    ],

    'patient_eligibility_statuses' => [
        'title' => 'حالات أهلية المرضى',
        'subtitle' => 'إدارة بيانات حالات أهلية المرضى المرجعية',
        'singular' => 'حالة أهلية مريض',
        'add' => 'إضافة حالة',
        'create_title' => 'إنشاء حالة أهلية مريض',
        'create_subtitle' => 'إضافة حالة أهلية مريض جديدة',
        'edit_title' => 'تعديل حالة أهلية مريض',
        'edit_subtitle' => 'تحديث معلومات حالة أهلية المريض',
        'show_title' => 'تفاصيل حالة أهلية المريض',
        'show_subtitle' => 'عرض معلومات حالة أهلية المريض',
    ],

    'patient_stages' => [
        'title' => 'مراحل المرضى',
        'subtitle' => 'إدارة بيانات مراحل المرضى المرجعية',
        'singular' => 'مرحلة مريض',
        'add' => 'إضافة مرحلة',
        'create_title' => 'إنشاء مرحلة مريض',
        'create_subtitle' => 'إضافة سجل مرحلة مريض جديد',
        'edit_title' => 'تعديل مرحلة مريض',
        'edit_subtitle' => 'تحديث معلومات مرحلة المريض',
        'show_title' => 'تفاصيل مرحلة المريض',
        'show_subtitle' => 'عرض معلومات مرحلة المريض',
    ],

    'activity_types' => [
        'title' => 'أنواع الأنشطة',
        'subtitle' => 'إدارة بيانات أنواع الأنشطة المرجعية',
        'singular' => 'نوع نشاط',
        'add' => 'إضافة نوع نشاط',
        'create_title' => 'إنشاء نوع نشاط',
        'create_subtitle' => 'إضافة سجل نوع نشاط جديد',
        'edit_title' => 'تعديل نوع نشاط',
        'edit_subtitle' => 'تحديث معلومات نوع النشاط',
        'show_title' => 'تفاصيل نوع النشاط',
        'show_subtitle' => 'عرض معلومات نوع النشاط',
    ],

    'transportation_locations' => [
        'title' => 'مواقع النقل',
        'subtitle' => 'إدارة بيانات مواقع النقل المرجعية',
        'singular' => 'موقع نقل',
        'add' => 'إضافة موقع',
        'create_title' => 'إنشاء موقع نقل',
        'create_subtitle' => 'إضافة موقع نقل جديد',
        'edit_title' => 'تعديل موقع نقل',
        'edit_subtitle' => 'تحديث معلومات موقع النقل',
        'show_title' => 'تفاصيل موقع النقل',
        'show_subtitle' => 'عرض معلومات موقع النقل',
    ],

    'attendance_statuses' => [
        'title' => 'حالات الحضور',
        'subtitle' => 'إدارة بيانات حالات الحضور المرجعية',
        'singular' => 'حالة حضور',
        'add' => 'إضافة حالة حضور',
        'create_title' => 'إنشاء حالة حضور',
        'create_subtitle' => 'إضافة سجل حالة حضور جديد',
        'edit_title' => 'تعديل حالة حضور',
        'edit_subtitle' => 'تحديث معلومات حالة الحضور',
        'show_title' => 'تفاصيل حالة الحضور',
        'show_subtitle' => 'عرض معلومات حالة الحضور',
    ],

    'campaign_statuses' => [
        'title' => 'حالات الحملات',
        'subtitle' => 'إدارة بيانات حالات الحملات المرجعية',
        'singular' => 'حالة حملة',
        'add' => 'إضافة حالة حملة',
        'create_title' => 'إنشاء حالة حملة',
        'create_subtitle' => 'إضافة سجل حالة حملة جديد',
        'edit_title' => 'تعديل حالة حملة',
        'edit_subtitle' => 'تحديث معلومات حالة الحملة',
        'show_title' => 'تفاصيل حالة الحملة',
        'show_subtitle' => 'عرض معلومات حالة الحملة',
    ],

    'implant_companies' => [
        'title' => 'شركات الزرع',
        'subtitle' => 'إدارة شركات زراعة القوقعة وأنواع الأقطاب',
        'singular' => 'شركة زرع',
        'add' => 'إضافة شركة',
        'create_title' => 'إنشاء شركة زرع',
        'create_subtitle' => 'إضافة شركة مع أنواع الأقطاب الخاصة بها',
        'edit_title' => 'تعديل شركة زرع',
        'edit_subtitle' => 'تحديث بيانات الشركة وأنواع الأقطاب',
        'show_title' => 'تفاصيل شركة الزرع',
        'show_subtitle' => 'عرض الشركة وقائمة أنواع الأقطاب',
        'electrode_types' => 'أنواع الأقطاب',
        'add_electrode' => 'إضافة نوع قطب',
        'electrode_types_hint' => 'تظهر أنواع الأقطاب في مرحلة العملية عند اختيار هذه الشركة.',
        'electrode_name_placeholder' => 'اسم نوع القطب',
    ],

    'insertion_approaches' => [
        'title' => 'طرق الإدخال',
        'subtitle' => 'إدارة خيارات طريقة الإدخال لمرحلة العملية',
        'singular' => 'طريقة إدخال',
        'add' => 'إضافة طريقة',
        'create_title' => 'إنشاء طريقة إدخال',
        'create_subtitle' => 'إضافة خيار جديد لطريقة الإدخال',
        'edit_title' => 'تعديل طريقة الإدخال',
        'edit_subtitle' => 'تحديث معلومات طريقة الإدخال',
        'show_title' => 'تفاصيل طريقة الإدخال',
        'show_subtitle' => 'عرض معلومات طريقة الإدخال',
    ],

    'ct_finding_options' => [
        'title' => 'خيارات نتائج CT',
        'subtitle' => 'إدارة خيارات نتائج CT للفحص التصويري',
        'singular' => 'خيار نتيجة CT',
        'add' => 'إضافة نتيجة CT',
        'create_title' => 'إنشاء خيار نتيجة CT',
        'create_subtitle' => 'إضافة خيار جديد لنتائج CT',
        'edit_title' => 'تعديل خيار نتيجة CT',
        'edit_subtitle' => 'تحديث معلومات خيار نتيجة CT',
        'show_title' => 'تفاصيل خيار نتيجة CT',
        'show_subtitle' => 'عرض معلومات خيار نتيجة CT',
    ],

    'mri_finding_options' => [
        'title' => 'خيارات نتائج MRI',
        'subtitle' => 'إدارة خيارات نتائج MRI للفحص التصويري',
        'singular' => 'خيار نتيجة MRI',
        'add' => 'إضافة نتيجة MRI',
        'create_title' => 'إنشاء خيار نتيجة MRI',
        'create_subtitle' => 'إضافة خيار جديد لنتائج MRI',
        'edit_title' => 'تعديل خيار نتيجة MRI',
        'edit_subtitle' => 'تحديث معلومات خيار نتيجة MRI',
        'show_title' => 'تفاصيل خيار نتيجة MRI',
        'show_subtitle' => 'عرض معلومات خيار نتيجة MRI',
    ],

    'expectation_post_ci_options' => [
        'title' => 'خيارات التوقعات بعد الزراعة',
        'subtitle' => 'إدارة خيارات التوقعات بعد الزراعة في الفحص',
        'singular' => 'خيار توقعات بعد الزراعة',
        'add' => 'إضافة خيار',
        'create_title' => 'إنشاء خيار توقعات بعد الزراعة',
        'create_subtitle' => 'إضافة خيار جديد للتوقعات بعد الزراعة',
        'edit_title' => 'تعديل خيار التوقعات بعد الزراعة',
        'edit_subtitle' => 'تحديث معلومات خيار التوقعات بعد الزراعة',
        'show_title' => 'تفاصيل خيار التوقعات بعد الزراعة',
        'show_subtitle' => 'عرض معلومات خيار التوقعات بعد الزراعة',
    ],
];
