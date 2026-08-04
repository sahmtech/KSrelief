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

    'brief' => [
        'title' => 'ملخص المريض السريري',
        'subtitle' => 'مراجعة سريرية قبل العملية',
        'preop_review' => 'مراجعة سريرية قبل العملية',
        'print' => 'طباعة',
        'full_profile' => 'الملف الكامل',
        'at_a_glance' => 'بنظرة واحدة',
        'at_a_glance_hint' => 'المعلومات الحاسمة لفريق العملية.',
        'clinical_alerts' => 'تنبيهات سريرية',
        'records_label' => 'السجلات الطبية',
        'surgery_context' => 'العملية والحالة',
        'priority_clinical' => 'البيانات السريرية الأساسية',
        'priority_clinical_hint' => 'أهم النتائج قبل دخول غرفة العمليات.',
        'demographics' => 'هوية المريض',
        'workflow_progress' => 'تقدّم المسار الطبي',
        'stage_records' => 'المسار السريري حسب المرحلة',
        'stage_records_hint' => 'أحدث إدخال مكتمل لكل مرحلة من مراحل العمل.',
        'stage_n' => 'المرحلة :n',
        'clinical_phases' => 'الملف السريري الكامل',
        'clinical_phases_hint' => 'الملف الكامل مجمّع حسب المرحلة السريرية.',
        'media' => 'الصور والفيديوهات والملفات',
        'upload_media' => 'رفع',
        'upload_files_label' => 'اختر الملفات',
        'upload_files_hint' => 'صور، فيديو (MP4, MOV, WebM)، أو مستندات. حتى 10 ملفات، 50 ميغا لكل ملف.',
        'upload_notes_placeholder' => 'ملاحظة اختيارية (مثل: أشعة، فيديو سمعي)',
        'no_media' => 'لا توجد صور أو فيديوهات أو ملفات مرفوعة بعد.',
        'photos_videos' => 'الصور والفيديوهات',
        'documents' => 'المستندات والملفات الأخرى',
        'records_snapshot' => ':total سجل طبي — يُعرض أحدث إدخال لكل مرحلة.',
        'records_history_hint' => 'الإدخالات الأقدم لنفس المرحلة لا تظهر في هذا الملخص. افتح الملف الكامل للسجل الكامل.',
        'view_all_records' => 'عرض كل السجلات',
        'stage_latest' => 'الأحدث: :date',
        'stage_record_count' => ':count سجل',
        'view_record' => 'فتح السجل',
        'show_more_fields' => 'عرض :count حقول إضافية',
        'show_less_fields' => 'عرض أقل',
        'collapse_phases' => 'طي',
        'expand_phases' => 'عرض الملف السريري الكامل',
    ],

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
        'screening' => 'الفحص والأهلية',
        'screening_hint' => 'بيانات الفحص الأولي (السمع، الكلام، الأهلية). جميع الحقول اختيارية.',
        'pre_op_create_hint' => 'اختياري. أي بيانات تُدخل هنا تُحفظ كسجل طبي لمرحلة ما قبل العملية ويمكن عرضها وتعديلها لاحقاً.',
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
        'file_number_auto' => 'يُولَّد تلقائياً: كود الدولة + السنة + الشهر + رقم تسلسلي (مثال: SYR2026071). فريد على مستوى النظام.',
        'file_number_generated_on_save' => 'يُولَّد تلقائياً عند الحفظ من دولة الحملة وتاريخ التسجيل. لا يمكن تعديله لاحقاً.',
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

    'operative_note_export' => [
        'button' => 'تصدير Operative Note',
        'modal_title' => 'تصدير Operative Note',
        'modal_subtitle' => 'تحميل تقرير أثناء العملية بصيغة PDF لهذا المريض.',
        'latest_label' => 'آخر عملية',
        'latest_badge' => 'الأحدث',
        'export_latest' => 'تصدير آخر Operative Note',
        'or_choose' => 'أو اختر عملية سابقة',
        'export_row' => 'تصدير',
        'columns' => [
            'date' => 'التاريخ',
            'surgeon' => 'الجراح',
            'side' => 'الجانب',
            'company' => 'الشركة',
            'implant' => 'الزرعة',
            'action' => 'إجراء',
        ],
    ],

    'import' => [
        'title' => 'استيراد المرضى',
        'subtitle' => 'استيراد البيانات الأساسية للمرضى لحملة محددة',
        'create_title' => 'استيراد مرضى',
        'create_subtitle' => 'اختر الحملة، ارفع نموذج Excel، واستورد المعلومات الأساسية للمرضى فقط',
        'show_title' => 'دفعة الاستيراد',
        'show_subtitle' => 'مراجعة واعتماد البيانات المستوردة',
        'download_template' => 'تحميل النموذج',
        'file_hint' => 'استخدم النموذج الرسمي (.xlsx). الصف 1 يجب أن يطابق عناوين الأعمدة بالضبط.',
        'column_required' => 'مطلوب',
        'column_optional' => 'اختياري',

        'template_table' => [
            'column' => 'العمود',
            'label' => 'الوصف',
            'required' => 'الإلزام',
            'rules' => 'التحقق',
        ],

        'review_table' => [
            'errors' => 'الأخطاء / ملاحظات',
        ],

        'column_hints' => [
            'patient_name' => 'اختياري. حد أقصى 255 حرفاً. إذا كان فارغاً يُستخدم اسم افتراضي عند الاعتماد.',
            'date_of_birth' => 'اختياري. YYYY-MM-DD أو تاريخ Excel. الافتراضي 2000-01-01 إذا كان فارغاً.',
            'gender' => 'مطلوب. استخدم: male أو female.',
            'height_cm' => 'اختياري. رقم بين 20 و 250.',
            'weight_kg' => 'اختياري. رقم بين 0.5 و 500.',
            'contact_number' => 'اختياري. حد أقصى 30 حرفاً.',
        ],

        'default_notes' => [
            'اسم فارغ → "مريض مستورد (صف X)" عند الاعتماد.',
            'تاريخ ميلاد فارغ → 2000-01-01 عند الاعتماد.',
            'حالة الأهلية الافتراضية: accepted.',
        ],

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
            'patient_name' => 'اسم المريض',
            'date_of_birth' => 'تاريخ الميلاد',
            'gender' => 'الجنس',
            'height_cm' => 'الطول (سم)',
            'weight_kg' => 'الوزن (كغ)',
            'contact_number' => 'رقم التواصل',
        ],

        'sections' => [
            'upload' => 'رفع الملف',
            'instructions' => 'التعليمات',
            'reference' => 'الرموز المرجعية',
            'template_columns' => 'أعمدة النموذج',
            'gender_values' => 'قيم الجنس',
            'defaults' => 'القيم الافتراضية',
            'statistics' => 'إحصائيات الاستيراد',
            'review' => 'مراجعة الصفوف',
            'approval' => 'الاعتماد',
        ],

        'instructions' => [
            'اختر الحملة المستهدفة قبل رفع الملف.',
            'حمّل نموذج Excel الرسمي واحتفظ بالصف 1 كما هو في النموذج.',
            'استخدم ورقة واحدة فقط بهذا ترتيب الأعمدة: patient_name, date_of_birth, gender, height_cm, weight_kg, contact_number.',
            'الجنس (gender) فقط هو الحقل المطلوب في كل صف. باقي الأعمدة اختيارية.',
            'إذا وُجدت قيمة في عمود، سيتم التحقق منها (صيغة التاريخ، الطول، الوزن، إلخ).',
            'الجنس يجب أن يكون: male أو female.',
            'صيغة تاريخ الميلاد: YYYY-MM-DD (مثال: 2015-06-20). خلايا التاريخ في Excel مقبولة أيضاً.',
            'الصفوف التي تحتوي على أخطاء تُرفض؛ الصفوف الصالحة يمكن اعتمادها.',
        ],

        'defaults' => [
            'unnamed_patient' => 'مريض مستورد (صف :row)',
        ],

        'campaign_workbook' => [
            'title' => 'استيراد ملف الحملة',
            'instructions' => [],
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
            'validation_failed_title' => 'تعذّر استيراد الملف',
            'empty_file' => 'الملف لا يحتوي على صفوف مرضى. الصف 1 يجب أن يكون العناوين وتبدأ بيانات المرضى من الصف 2. الأعمدة المطلوبة: :required. ترتيب الأعمدة المتوقع: :columns.',
            'file_missing' => 'ملف الاستيراد غير موجود في التخزين.',
            'header_missing' => 'الصف 1 فارغ أو لا يحتوي على عناوين الأعمدة. انسخ صف العناوين من نموذج الاستيراد دون تغيير الأسماء أو الترتيب. الأعمدة المتوقعة: :columns. الأعمدة المطلوبة: :required.',
            'invalid_template' => 'الملف لا يطابق نموذج الاستيراد. أعمدة ناقصة: :missing. أعمدة زائدة: :extra. الترتيب المتوقع: :expected. الموجود في الصف 1: :found. الحقل المطلوب في كل صف: :required. حمّل النموذج والصق البيانات من الصف 2 دون تغيير العناوين.',
            'invalid_template_order' => 'ترتيب الأعمدة في الصف 1 غير صحيح. الترتيب المتوقع: :expected. الموجود: :found. لا تغيّر أسماء أو ترتيب عناوين النموذج.',
            'missing_column' => 'العمود المطلوب ":column" مفقود من الملف.',
            'processing_failed' => 'حدث خطأ غير متوقع أثناء معالجة الملف. حاول مرة أخرى أو تواصل مع الدعم.',
            'invalid_number' => '":field" يجب أن يكون رقماً.',
            'out_of_range' => '":field" يجب أن يكون بين :min و :max.',
            'too_long' => '":field" يجب ألا يتجاوز :max حرفاً.',
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
            'campaign_required' => 'يرجى اختيار حملة قبل الرفع.',
            'workbook_not_supported' => 'يبدو أن هذا الملف workbook للحملة (الأوراق الموجودة: :sheets). استيراد المرضى يقبل فقط النموذج الأساسي بورقة واحدة. حمّل النموذج واستخدم ورقة واحدة بهذا ترتيب الأعمدة: :columns. الأعمدة المطلوبة: :required.',
            'no_importable_rows' => 'لا توجد صفوف صالحة للاستيراد.',
            'confirm_approve' => 'اعتماد هذا الاستيراد؟ سيؤدي ذلك إلى إنشاء :count سجل مريض ولا يمكن التراجع عنه.',
        ],
    ],

    'export' => [
        'create_title' => 'تصدير المرضى',
        'create_subtitle' => 'تنزيل بيانات المرضى لحملة محددة',

        'fields' => [
            'campaign' => 'الحملة',
            'all_stages' => 'جميع مراحل السجلات الطبية',
        ],

        'sections' => [
            'options' => 'خيارات التصدير',
            'medical_records' => 'السجلات الطبية',
            'instructions' => 'التعليمات',
            'structure' => 'بنية الملف',
        ],

        'sheets' => [
            'patients' => 'المرضى',
        ],

        'hints' => [
            'basic_sheet' => 'ورقة المرضى تُضمَّن دائماً وتتضمن المعلومات الأساسية وسير العمل.',
            'multiple_records' => 'إذا كان للمريض أكثر من سجل لنفس المرحلة، يُصدَّر كل سجل في صف منفصل مع رقم record_sequence.',
        ],

        'instructions' => [
            'اختر الحملة التي تريد تصدير مرضاها.',
            'تُصدَّر المعلومات الأساسية دائماً في ورقة المرضى.',
            'يمكنك اختيار مرحلة أو أكثر من السجلات الطبية، أو اختيار جميع المراحل.',
            'اترك جميع خانات المراحل فارغة لتصدير البيانات الأساسية فقط.',
            'كل مرحلة مختارة تحصل على ورقة Excel خاصة بها.',
        ],

        'structure' => [
            'ورقة المرضى: رقم الملف، الاسم، تاريخ الميلاد، الجنس، التواصل، الأهلية، القبول، المرحلة الحالية، يوم العملية، الترتيب، الجانب، والملاحظات.',
            'أوراق المراحل: معرّفات المريض، record_sequence، تاريخ السجل، المُدخِل، وجميع حقول المرحلة كنص مقروء.',
            'record_sequence يبدأ من 1 لكل مريض ضمن نفس المرحلة ويزيد مع كل سجل إضافي.',
        ],

        'actions' => [
            'download' => 'تنزيل Excel',
        ],

        'messages' => [
            'failed' => 'فشل التصدير. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.',
        ],
    ],

    'messages' => [
        'created' => 'تم تسجيل المريض بنجاح.',
        'updated' => 'تم تحديث المريض بنجاح.',
        'deleted' => 'تم حذف المريض بنجاح.',
        'attachment_uploaded' => 'تم رفع المرفق بنجاح.',
        'attachment_required' => 'يرجى اختيار ملف واحد على الأقل للرفع.',
        'attachment_deleted' => 'تم إزالة المرفق بنجاح.',
        'confirm_delete' => 'هل أنت متأكد من حذف سجل هذا المريض؟',
        'confirm_remove_attachment' => 'إزالة هذا المرفق؟',
        'empty' => 'لا يوجد مرضى.',
        'no_attachments' => 'لا توجد مرفقات.',
    ],
];
