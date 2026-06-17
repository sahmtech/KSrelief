<?php

return [
    'title' => 'النقل',
    'subtitle' => 'إدارة رحلات النقل للحملات',
    'create_title' => 'إنشاء رحلة',
    'create_subtitle' => 'جدولة رحلة نقل جديدة',
    'edit_title' => 'تعديل الرحلة',
    'show_title' => 'تفاصيل الرحلة',
    'passengers_title' => 'إدارة الركاب',

    'stats' => [
        'total' => 'إجمالي الرحلات',
        'today' => 'رحلات اليوم',
        'upcoming' => 'الرحلات القادمة',
        'completed' => 'الرحلات المكتملة',
        'patients_transported' => 'المرضى المنقولون',
        'members_transported' => 'الأعضاء المنقولون',
        'passengers_transported' => 'إجمالي الركاب المنقولين',
    ],

    'status' => [
        'planned' => 'مخطط',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ],

    'trip_type' => [
        'patient_transport' => 'نقل مرضى',
        'member_transport' => 'نقل أعضاء',
        'mixed_transport' => 'نقل مختلط',
    ],

    'passenger_type' => [
        'member' => 'عضو',
        'patient' => 'مريض',
    ],

    'table' => [
        'trip_code' => 'رمز الرحلة',
        'campaign' => 'الحملة',
        'trip_date' => 'تاريخ الرحلة',
        'from' => 'من',
        'to' => 'إلى',
        'type' => 'النوع',
        'passengers' => 'الركاب',
        'status' => 'الحالة',
        'created_by' => 'أنشئ بواسطة',
        'actions' => 'الإجراءات',
        'departure' => 'المغادرة',
        'arrival' => 'الوصول',
        'passenger' => 'الراكب',
        'passenger_type' => 'النوع',
    ],

    'filters' => [
        'search' => 'بحث',
        'search_placeholder' => 'رمز الرحلة، المركبة، السائق…',
        'campaign' => 'الحملة',
        'all_campaigns' => 'جميع الحملات',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'trip_type' => 'نوع الرحلة',
        'all_types' => 'جميع الأنواع',
        'status' => 'الحالة',
        'all_statuses' => 'جميع الحالات',
        'from_location' => 'من موقع',
        'to_location' => 'إلى موقع',
        'all_locations' => 'جميع المواقع',
        'apply' => 'تطبيق',
        'reset' => 'إعادة تعيين',
    ],

    'fields' => [
        'campaign' => 'الحملة',
        'trip_date' => 'تاريخ الرحلة',
        'departure_time' => 'وقت المغادرة',
        'arrival_time' => 'وقت الوصول',
        'from_location' => 'من موقع',
        'to_location' => 'إلى موقع',
        'trip_type' => 'نوع الرحلة',
        'vehicle_number' => 'رقم المركبة',
        'driver_name' => 'اسم السائق',
        'capacity' => 'السعة',
        'notes' => 'ملاحظات',
        'status' => 'الحالة',
        'passenger_type' => 'نوع الراكب',
        'member' => 'العضو',
        'patient' => 'المريض',
        'vehicle_info' => 'معلومات المركبة',
        'trip_info' => 'معلومات الرحلة',
        'timeline' => 'الجدول الزمني للحالة',
    ],

    'actions' => [
        'create' => 'إنشاء رحلة',
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'add_patient' => 'إضافة مريض',
        'add_member' => 'إضافة عضو',
        'remove_passenger' => 'إزالة',
        'start_trip' => 'بدء الرحلة',
        'complete_trip' => 'إكمال الرحلة',
        'cancel_trip' => 'إلغاء الرحلة',
        'manage_passengers' => 'إدارة الركاب',
    ],

    'messages' => [
        'created' => 'تم إنشاء رحلة النقل بنجاح.',
        'updated' => 'تم تحديث رحلة النقل بنجاح.',
        'deleted' => 'تم حذف رحلة النقل بنجاح.',
        'status_changed' => 'تم تحديث حالة الرحلة بنجاح.',
        'passenger_added' => 'تمت إضافة الراكب بنجاح.',
        'passenger_removed' => 'تمت إزالة الراكب بنجاح.',
        'trip_created' => 'تم إنشاء الرحلة بحالة مخطط.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذه الرحلة؟',
        'confirm_remove_passenger' => 'إزالة هذا الراكب من الرحلة؟',
        'confirm_start' => 'بدء هذه الرحلة الآن؟',
        'confirm_complete' => 'تعيين هذه الرحلة كمكتملة؟',
        'confirm_cancel' => 'إلغاء هذه الرحلة؟',
        'no_passengers' => 'لا يوجد ركاب معينون لهذه الرحلة بعد.',
        'no_trips' => 'لا توجد رحلات نقل.',
    ],

    'errors' => [
        'member_not_assigned' => 'العضو غير معين على حملة الرحلة.',
        'patient_not_in_campaign' => 'المريض لا ينتمي إلى حملة الرحلة.',
        'passenger_already_on_trip' => 'هذا الراكب موجود بالفعل على الرحلة.',
        'trip_not_editable' => 'لا يمكن تعديل هذه الرحلة.',
        'invalid_status_transition' => 'انتقال حالة غير صالح.',
        'invalid_passenger_for_trip_type' => 'نوع الراكب غير مسموح لهذا النوع من الرحلة.',
        'capacity_reached' => 'تم الوصول إلى سعة الرحلة.',
        'cannot_delete_in_progress' => 'لا يمكن حذف رحلة قيد التنفيذ.',
    ],

    'campaign' => [
        'title' => 'نقل الحملة',
        'recent_trips' => 'أحدث الرحلات',
    ],

    'patient' => [
        'title' => 'سجل النقل',
        'trip_history' => 'سجل الرحلات',
    ],

    'member' => [
        'title' => 'النقل',
        'assigned_trips' => 'الرحلات المعينة',
        'upcoming' => 'الرحلات القادمة',
    ],
];
