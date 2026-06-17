<?php

return [

    'title'       => 'سير العمل الطبي',
    'stage_history' => 'سجل المراحل',
    'medical_records' => 'السجلات الطبية',
    'current_stage' => 'المرحلة الحالية',
    'change_stage'  => 'تغيير المرحلة',
    'last_updated'  => 'آخر تحديث',
    'no_stage'      => 'لا توجد مرحلة محددة',
    'no_history'    => 'لا يوجد سجل تغييرات للمراحل بعد.',
    'no_records'    => 'لا توجد سجلات طبية لهذا المريض.',

    'timeline' => [
        'title'      => 'المخطط الزمني',
        'completed'  => 'مكتمل',
        'current'    => 'المرحلة الحالية',
        'pending'    => 'قيد الانتظار',
        'changed_by' => 'بواسطة',
    ],

    'change_stage_modal' => [
        'title'       => 'تغيير مرحلة المريض',
        'new_stage'   => 'المرحلة الجديدة',
        'notes'       => 'ملاحظات',
        'notes_hint'  => 'ملاحظات اختيارية حول هذا التغيير.',
        'confirm'     => 'تغيير المرحلة',
        'cancel'      => 'إلغاء',
    ],

    'history' => [
        'date'       => 'التاريخ',
        'from_stage' => 'من مرحلة',
        'to_stage'   => 'إلى مرحلة',
        'changed_by' => 'تم التغيير بواسطة',
        'notes'      => 'ملاحظات',
    ],

    'records' => [
        'date'         => 'التاريخ',
        'stage'        => 'المرحلة',
        'submitted_by' => 'تم الإدخال بواسطة',
        'actions'      => 'إجراءات',
        'add'          => 'إضافة سجل طبي',
        'view'         => 'عرض',
        'edit'         => 'تعديل',
        'delete'       => 'حذف',
    ],

    'fields' => [
        'admission_notes'    => 'ملاحظات القبول',
        'initial_assessment' => 'التقييم الأولي',
        'weight'           => 'الوزن (كغ)',
        'anesthesia_notes' => 'ملاحظات التخدير',
        'readiness_status' => 'حالة الاستعداد',
        'comments'         => 'تعليقات',
        'operation_date'   => 'تاريخ العملية',
        'start_time'       => 'وقت البدء',
        'end_time'         => 'وقت الانتهاء',
        'surgeon'          => 'الجراح',
        'side'             => 'الجهة',
        'electrode_type'   => 'نوع القطب',
        'insertion_type'   => 'نوع الإدخال',
        'operation_notes'  => 'ملاحظات العملية',
        'post_op_xray'     => 'صورة أشعة ما بعد العملية',
        'findings'         => 'النتائج',
        'complications'    => 'المضاعفات',
        'recommendations'  => 'التوصيات',
        'activation_date'   => 'تاريخ التفعيل',
        'activation_result' => 'نتيجة التفعيل',
        'session_date'    => 'تاريخ الجلسة',
        'education_notes' => 'ملاحظات التأهيل التعليمي',
        'rehab_plan'      => 'خطة إعادة التأهيل',
        'outcome'         => 'النتيجة',
        'coordinator'     => 'المنسق',
        'attending_doctor' => 'الطبيب المعالج',
        'specialist'      => 'الأخصائي',
        'admission_attachments' => 'مرفقات القبول',
        'admission_attachments_hint' => 'ملفات اختيارية تُربط بسجل المريض (PDF، صور، مستندات).',
    ],

    'sides' => [
        'left'      => 'يسار',
        'right'     => 'يمين',
        'bilateral' => 'ثنائي',
    ],

    'messages' => [
        'stage_changed'  => 'تم تغيير المرحلة بنجاح.',
        'initial_stage'  => 'دخول المريض المرحلة الأولى في سير العمل.',
        'demo_stage_transition' => 'الانتقال إلى التخدير بعد اكتمال القبول.',
        'stage_change_via_workflow' => 'يجب تغيير المرحلة من تبويب سير العمل الطبي.',
        'no_campaign_members' => 'لا يوجد أعضاء فريق للحملة لهذا الدور.',
        'record_created' => 'تم إنشاء السجل الطبي بنجاح.',
        'record_updated' => 'تم تحديث السجل الطبي بنجاح.',
        'record_deleted' => 'تم حذف السجل الطبي بنجاح.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا السجل الطبي؟ لا يمكن التراجع عن هذا الإجراء.',
    ],

    'errors' => [
        'same_stage' => 'لا يمكن نقل المريض إلى نفس المرحلة.',
    ],

    'validation' => [
        'stage_required'  => 'الرجاء اختيار مرحلة جديدة.',
        'stage_not_found' => 'المرحلة المحددة غير موجودة.',
    ],

];
