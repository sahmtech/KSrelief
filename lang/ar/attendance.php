<?php

return [
    'title' => 'الحضور',
    'subtitle' => 'إدارة حضور طاقم الحملة',
    'quick_title' => 'الحضور السريع',
    'quick_subtitle' => 'تسجيل حضور جميع أعضاء الحملة',
    'grid_title' => 'جدول حضور الفريق',
    'create_title' => 'تسجيل حضور',
    'create_subtitle' => 'تسجيل دخول عضو في الحملة',
    'edit_title' => 'تعديل الحضور',
    'show_title' => 'سجل الحضور',

    'stats' => [
        'total' => 'إجمالي السجلات',
        'present_today' => 'حاضر اليوم',
        'late_today' => 'متأخر اليوم',
        'absent_today' => 'غائب اليوم',
        'leave_today' => 'إجازة اليوم',
        'attendance_rate' => 'نسبة الحضور',
        'present' => 'حاضر',
        'late' => 'متأخر',
        'absent' => 'غائب',
        'leave' => 'إجازة',
    ],

    'table' => [
        'date' => 'التاريخ',
        'campaign' => 'الحملة',
        'member' => 'العضو',
        'role' => 'الدور',
        'specialty' => 'التخصص',
        'shift' => 'الوردية',
        'check_in' => 'دخول',
        'check_out' => 'خروج',
        'worked_hours' => 'ساعات العمل',
        'status' => 'الحالة',
        'recorded_by' => 'سجّله',
        'actions' => 'إجراءات',
    ],

    'filters' => [
        'search' => 'بحث',
        'search_placeholder' => 'اسم العضو، الجوال، البريد…',
        'campaign' => 'الحملة',
        'all_campaigns' => 'كل الحملات',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'shift' => 'الوردية',
        'all_shifts' => 'كل الورديات',
        'status' => 'الحالة',
        'all_statuses' => 'كل الحالات',
        'role' => 'الدور',
        'all_roles' => 'كل الأدوار',
        'specialty' => 'التخصص',
        'all_specialties' => 'كل التخصصات',
        'apply' => 'تطبيق',
        'reset' => 'إعادة تعيين',
    ],

    'fields' => [
        'campaign' => 'الحملة',
        'member' => 'العضو',
        'date' => 'التاريخ',
        'shift' => 'الوردية',
        'check_in' => 'وقت الدخول',
        'check_out' => 'وقت الخروج',
        'status' => 'حالة الحضور',
        'notes' => 'ملاحظات',
        'worked_hours' => 'ساعات العمل',
        'recorded_by' => 'سجّله',
    ],

    'actions' => [
        'record' => 'تسجيل حضور',
        'quick' => 'الحضور السريع',
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'save_all' => 'حفظ الكل',
        'load_members' => 'تحميل الأعضاء',
    ],

    'messages' => [
        'created' => 'تم تسجيل الحضور بنجاح.',
        'updated' => 'تم تحديث الحضور بنجاح.',
        'deleted' => 'تم حذف سجل الحضور بنجاح.',
        'bulk_saved' => 'تم إنشاء :created وتحديث :updated.',
        'confirm_delete' => 'هل أنت متأكد من حذف سجل الحضور هذا؟',
        'no_members' => 'لا يوجد أعضاء معيّنون لهذه الحملة.',
        'select_campaign' => 'اختر حملة لتحميل الأعضاء.',
    ],

    'errors' => [
        'member_not_assigned' => 'العضو غير معيّن للحملة المحددة.',
        'duplicate_record' => 'يوجد سجل حضور لهذا العضو في نفس التاريخ والوردية.',
    ],

    'member' => [
        'summary' => 'ملخص الحضور',
        'recent' => 'آخر سجلات الحضور',
        'rate' => 'نسبة الحضور',
    ],

    'campaign' => [
        'title' => 'حضور الحملة',
        'daily_list' => 'الحضور اليومي',
        'quick_link' => 'فتح الحضور السريع',
    ],
];
