<?php

return [
    'title' => 'المرضى',
    'subtitle' => 'سجل المرضى والمسار الطبي',
    'add' => 'تسجيل مريض',
    'create_title' => 'تسجيل مريض',
    'create_subtitle' => 'إضافة مريض جديد لحملة',
    'edit_title' => 'تعديل المريض',
    'edit_subtitle' => 'تحديث بيانات المريض',
    'show_title' => 'ملف المريض',
    'show_subtitle' => 'تفاصيل المريض والتصنيف الطبي',

    'show' => [
        'back' => 'العودة إلى المريض',
    ],

    'admission' => [
        'admitted' => 'مُقبول',
        'not_admitted' => 'غير مُقبول',
    ],

    'record_status' => [
        'active' => 'نشط',
        'closed' => 'مغلق',
        'archived' => 'مؤرشف',
    ],

    'age' => [
        'years' => ':years سنة',
        'months' => ':months شهر',
    ],

    'stats' => [
        'total' => 'إجمالي المرضى',
        'accepted' => 'مقبول',
        'rejected' => 'مرفوض',
        'postponed' => 'مؤجل',
        'cancelled' => 'ملغى',
        'admitted' => 'مُدخل',
        'completed' => 'مكتمل',
    ],

    'filters' => [
        'title' => 'التصفية',
        'search' => 'بحث',
        'search_placeholder' => 'الاسم، رقم الملف، التواصل…',
        'campaign' => 'الحملة',
        'all_campaigns' => 'جميع الحملات',
        'eligibility' => 'حالة الأهلية',
        'all_eligibility' => 'جميع الحالات',
        'stage' => 'المرحلة الحالية',
        'all_stages' => 'جميع المراحل',
        'admission' => 'حالة القبول',
        'all_admission' => 'الكل',
        'gender' => 'الجنس',
        'all_genders' => 'الكل',
        'created_from' => 'من تاريخ',
        'created_to' => 'إلى تاريخ',
        'apply' => 'تطبيق',
        'reset' => 'إعادة تعيين',
    ],

    'table' => [
        'name' => 'اسم المريض',
        'file_number' => 'كود المريض',
        'campaign' => 'الحملة',
        'age' => 'العمر',
        'gender' => 'الجنس',
        'eligibility' => 'الأهلية',
        'stage' => 'المرحلة',
        'admission' => 'القبول',
        'created_at' => 'تاريخ الإنشاء',
        'actions' => 'الإجراءات',
    ],

    'sections' => [
        'basic' => 'المعلومات الأساسية',
        'campaign' => 'معلومات الحملة',
        'medical' => 'التصنيف الطبي',
        'contact' => 'معلومات التواصل',
        'attachments' => 'المرفقات',
        'screening' => 'الفحص قبل العملية',
        'screening_hint' => 'بيانات الفحص والتصوير (الحقول الصفراء/البرتقالية في ملف الحملة). للملفات الكبيرة، الصق رابط Google Drive بدل الرفع.',
        'audit' => 'معلومات السجل',
    ],

    'fields' => [
        'campaign' => 'الحملة',
        'patient_name' => 'اسم المريض',
        'photo' => 'صورة المريض',
        'file_number' => 'كود المريض',
        'date_of_birth' => 'تاريخ الميلاد',
        'age' => 'العمر',
        'gender' => 'الجنس',
        'contact_number' => 'رقم التواصل',
        'eligibility_status' => 'حالة الأهلية',
        'current_stage' => 'المرحلة الحالية',
        'admission_status' => 'حالة القبول',
        'record_status' => 'حالة السجل',
        'notes' => 'ملاحظات',
        'created_by' => 'أنشئ بواسطة',
        'updated_by' => 'عُدّل بواسطة',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'attachment' => 'مرفق',
        'attachment_notes' => 'ملاحظات المرفق',
        'surgery_day_number' => 'يوم الجراحة',
        'surgery_day_number_value' => 'اليوم :day',
        'rank' => 'الترتيب',
        'surgical_side' => 'جانب العملية',
        'approval_reason' => 'سبب القبول / الرفض',
        'height_cm' => 'الطول (سم)',
        'weight_kg' => 'الوزن (كغ)',
    ],

    'measurements' => [
        'height_value' => ':value سم',
        'weight_value' => ':value كغ',
    ],

    'hints' => [
        'file_number_auto' => 'يُولَّد تلقائياً بصيغة: اختصار الدولة-كود الحملة-رقم تسلسلي (مثال: NG-SAMA-001).',
        'file_number_generated_on_save' => 'يُولَّد تلقائياً عند الحفظ ولا يمكن تعديله لاحقاً.',
    ],

    'placeholders' => [
        'patient_name' => 'الاسم الكامل للمريض',
        'file_number' => 'مثال: P-2026-001',
        'contact_number' => 'مثال: +966501234567',
        'height_cm' => 'مثال: 120',
        'weight_kg' => 'مثال: 32.5',
        'notes' => 'ملاحظات سريرية أو إدارية…',
        'select_campaign' => 'اختر الحملة',
        'select_eligibility' => 'اختر حالة الأهلية',
        'select_stage' => 'اختر المرحلة',
    ],

    'tabs' => [
        'overview' => 'نظرة عامة',
        'clinical' => 'الملف السريري',
        'workflow' => 'المسار الطبي',
        'records' => 'السجلات الطبية',
        'history' => 'سجل المراحل',
        'attachments' => 'المرفقات',
        'reports' => 'التقارير',
        'transportation' => 'النقل',
        'activities' => 'الأنشطة',
    ],

    'clinical' => [
        'title' => 'الملف السريري',
        'subtitle' => 'بيانات المريض الكاملة مقسّمة حسب ما قبل / أثناء / بعد العملية.',
        'field' => 'الحقل',
        'value' => 'القيمة',
        'source' => 'المصدر',
        'no_phase_data' => 'لا توجد بيانات لهذه المرحلة بعد.',
        'source_screening' => 'الفحص',
        'source_patient' => 'ملف المريض',
        'edit_screening' => 'تعديل بيانات الفحص',
        'open_link' => 'فتح الرابط',
    ],

    'future' => [
        'workflow' => 'سيُتاح المسار الطبي عند تفعيل وحدة سير العمل.',
        'records' => 'ستظهر السجلات الطبية عند تفعيل وحدة السجلات الطبية.',
        'history' => 'سيظهر سجل المراحل عند تفعيل وحدة تاريخ المراحل.',
        'reports' => 'ستظهر تقارير المريض عند تفعيل وحدة التقارير.',
    ],

    'photo' => [
        'choose' => 'اختر صورة',
        'change' => 'تغيير الصورة',
        'remove' => 'إزالة الصورة',
        'hint' => 'اختياري. JPEG أو PNG أو WebP، بحد أقصى 2 ميجابايت.',
    ],

    'campaign' => [
        'title' => 'مرضى الحملة',
        'add_patient' => 'إضافة مريض',
        'empty' => 'لا يوجد مرضى مسجلون لهذه الحملة بعد.',
    ],

    'actions' => [
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'download' => 'تحميل',
        'upload' => 'رفع مرفق',
        'remove_attachment' => 'إزالة',
    ],

    'import' => [
        'title' => 'استيراد المرضى',
        'subtitle' => 'استيراد المرضى من جداول Excel',
        'create_title' => 'استيراد مرضى',
        'create_subtitle' => 'رفع ملف Excel لاستيراد المرضى بشكل جماعي',
        'show_title' => 'دفعة الاستيراد',
        'show_subtitle' => 'مراجعة واعتماد البيانات المستوردة',
        'download_template' => 'تحميل النموذج',

        'status' => [
            'uploaded' => 'مرفوع',
            'processing' => 'قيد المعالجة',
            'review' => 'في انتظار المراجعة',
            'approved' => 'معتمد',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
        ],

        'stats' => [
            'total' => 'إجمالي الاستيرادات',
            'pending_review' => 'في انتظار المراجعة',
            'processing' => 'قيد المعالجة',
            'completed' => 'مكتملة',
            'failed' => 'فاشلة',
            'patients_imported' => 'مرضى تم استيرادهم',
        ],

        'table' => [
            'date' => 'تاريخ الاستيراد',
            'campaign' => 'الحملة',
            'uploaded_by' => 'رُفع بواسطة',
            'rows' => 'الصفوف',
            'valid' => 'صالحة',
            'invalid' => 'غير صالحة',
            'duplicates' => 'مكررة',
            'imported' => 'مستوردة',
            'status' => 'الحالة',
            'actions' => 'الإجراءات',
        ],

        'row_status' => [
            'valid' => 'صالح',
            'invalid' => 'غير صالح',
            'duplicate' => 'مكرر',
            'imported' => 'مستورد',
        ],

        'fields' => [
            'campaign' => 'الحملة',
            'file' => 'ملف Excel',
            'notes' => 'ملاحظات',
        ],

        'sections' => [
            'upload' => 'رفع الملف',
            'instructions' => 'التعليمات',
            'reference' => 'الرموز المرجعية',
            'statistics' => 'إحصائيات الاستيراد',
            'review' => 'مراجعة الصفوف',
            'approval' => 'الاعتماد',
        ],

        'instructions' => [
            'حمّل نموذج Excel واملأ بيانات المرضى.',
            'حقل campaign_code مطلوب ويجب أن يطابق رمز حملة موجودة.',
            'يجب استخدام الرموز المرجعية في حقلَي eligibility_status وadmission_status.',
            'صيغة تاريخ الميلاد: YYYY-MM-DD (مثال: 2015-06-20).',
            'الجنس يجب أن يكون: male أو female.',
            'حقل stage اختياري — اتركه فارغاً لاستخدام مرحلة القبول الافتراضية.',
            'رقم الملف يجب أن يكون فريداً داخل نفس الحملة.',
            'الصفوف التي تحتوي على أخطاء سيتم رفضها — الصفوف الصالحة يمكن اعتمادها.',
            'سيتم معالجة الاستيراد في الخلفية. أعِد تحميل الصفحة للتحقق من الحالة.',
        ],

        'campaign_workbook' => [
            'title' => 'استيراد ملف الحملة',
            'instructions' => [
                'ارفع ملف Excel الكامل للحملة (مثل Main Patients File مع Day1 وDay2 وDay3).',
                'اختر الحملة المستهدفة قبل الرفع — مطلوب لاستيراد ملف الحملة.',
                'ورقة Main Patients File تستورد بيانات الفحص والمعلومات الأساسية.',
                'أوراق الأيام تستورد يوم العملية والترتيب والحقول السريرية.',
                'يتم ربط المرضى بين الأوراق بالاسم.',
                'حالة الموافقة تُحوَّل تلقائياً (accepted، waiting list، rejected، إلخ).',
                'الصفوف الناقصة تاريخ الميلاد أو الجنس ستُعلَّم للمراجعة.',
            ],
        ],

        'actions' => [
            'upload' => 'رفع الملف',
            'approve' => 'اعتماد الاستيراد',
            'download_errors' => 'تصدير الأخطاء',
            'view' => 'عرض',
        ],

        'messages' => [
            'uploaded' => 'تم رفع الملف وإضافته لقائمة المعالجة. أعِد التحميل للتحقق من الحالة.',
            'processing_wait' => 'جاري معالجة الملف. ستُحدَّث الصفحة تلقائياً.',
            'queued_wait' => 'في انتظار بدء المعالجة. ستُحدَّث الصفحة تلقائياً.',
            'queue_stuck' => 'المعالجة تأخذ وقتاً أطول من المتوقع. اطلب من المسؤول تشغيل queue worker، أو ضع PATIENT_IMPORT_SYNC=true في ملف .env.',
            'approved' => 'تم استيراد :count مريض/مرضى بنجاح.',
            'not_reviewable' => 'هذه الدفعة ليست في حالة قابلة للمراجعة.',
            'empty_file' => 'الملف لا يحتوي على بيانات.',
            'file_missing' => 'ملف الاستيراد غير موجود في التخزين.',
            'missing_column' => 'العمود المطلوب ":column" مفقود من الملف.',
            'required' => 'الحقل ":field" مطلوب.',
            'invalid_date' => 'صيغة التاريخ غير صالحة. استخدم YYYY-MM-DD.',
            'future_date' => 'تاريخ الميلاد لا يمكن أن يكون في المستقبل.',
            'invalid_gender' => 'الجنس غير صالح. استخدم: male أو female.',
            'invalid_eligibility' => 'رمز حالة الأهلية غير صالح: :code',
            'invalid_admission' => 'حالة القبول غير صالحة. استخدم: admitted أو not_admitted.',
            'invalid_stage' => 'رمز المرحلة غير صالح: :code',
            'invalid_campaign' => 'لم يتم العثور على الحملة للرمز: :code',
            'campaign_mismatch' => 'رمز الحملة لا يطابق الحملة المحددة (المتوقع: :expected).',
            'duplicate_in_file' => 'حقل :field مكرر في الملف (ظهر لأول مرة في الصف :row).',
            'duplicate_in_database' => 'كود المريض ":file_number" موجود بالفعل في النظام.',
            'duplicate_name_in_database' => 'المريض ":name" موجود بالفعل في هذه الحملة.',
            'campaign_required_workbook' => 'يجب اختيار حملة عند استيراد ملف حملة Excel.',
            'no_importable_rows' => 'لا توجد صفوف صالحة للاستيراد.',
            'confirm_approve' => 'اعتماد هذا الاستيراد؟ سيؤدي ذلك إلى إنشاء :count سجل مريض ولا يمكن التراجع عنه.',
        ],
    ],

    'messages' => [
        'created' => 'تم تسجيل المريض بنجاح.',
        'updated' => 'تم تحديث المريض بنجاح.',
        'deleted' => 'تم حذف المريض بنجاح.',
        'attachment_uploaded' => 'تم رفع المرفق بنجاح.',
        'attachment_deleted' => 'تم إزالة المرفق بنجاح.',
        'confirm_delete' => 'هل أنت متأكد من حذف سجل هذا المريض؟',
        'confirm_remove_attachment' => 'إزالة هذا المرفق؟',
        'empty' => 'لا يوجد مرضى.',
        'no_attachments' => 'لا توجد مرفقات.',
    ],
];
