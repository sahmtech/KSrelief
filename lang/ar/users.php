<?php

return [
    'title' => 'المستخدمون',
    'subtitle' => 'إدارة مستخدمي النظام',
    'add' => 'إضافة مستخدم',
    'create_title' => 'إنشاء مستخدم',
    'create_subtitle' => 'إضافة مستخدم جديد وتعيين الأدوار',
    'edit_title' => 'تعديل مستخدم',
    'edit_subtitle' => 'تحديث بيانات المستخدم وتعيين الأدوار',
    'show_title' => 'تفاصيل المستخدم',
    'show_subtitle' => 'عرض معلومات الحساب والصلاحيات',

    'fields' => [
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'mobile' => 'رقم الجوال',
        'gender' => 'الجنس',
        'status' => 'الحالة',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'roles' => 'الأدوار',
        'last_login' => 'آخر تسجيل دخول',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
        'email_verified' => 'البريد موثّق',
        'avatar' => 'الصورة الشخصية',
    ],

    'gender' => [
        'male' => 'ذكر',
        'female' => 'أنثى',
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'suspended' => 'موقوف',
    ],

    'sections' => [
        'personal' => 'المعلومات الشخصية',
        'account' => 'حالة الحساب',
        'roles' => 'الأدوار المعيّنة',
        'permissions' => 'الصلاحيات الممنوحة',
        'photo' => 'الصورة الشخصية',
    ],

    'table' => [
        'name' => 'الاسم',
        'email' => 'البريد',
        'mobile' => 'الجوال',
        'role' => 'الدور',
        'status' => 'الحالة',
        'last_login' => 'آخر دخول',
        'created_at' => 'تاريخ الإنشاء',
        'actions' => 'الإجراءات',
    ],

    'filters' => [
        'title' => 'تصفية',
        'role' => 'الدور',
        'status' => 'الحالة',
        'all_roles' => 'جميع الأدوار',
        'all_statuses' => 'جميع الحالات',
        'apply' => 'تطبيق',
        'reset' => 'إعادة تعيين',
    ],

    'actions' => [
        'view' => 'عرض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'activate' => 'تفعيل',
        'deactivate' => 'إلغاء التفعيل',
        'change_password' => 'تغيير كلمة المرور',
    ],

    'change_password' => [
        'title' => 'تغيير كلمة المرور',
        'subtitle' => 'تعيين كلمة مرور جديدة لهذا المستخدم',
        'submit' => 'تحديث كلمة المرور',
    ],

    'messages' => [
        'created' => 'تم إنشاء المستخدم بنجاح.',
        'updated' => 'تم تحديث المستخدم بنجاح.',
        'deleted' => 'تم حذف المستخدم بنجاح.',
        'activated' => 'تم تفعيل المستخدم بنجاح.',
        'deactivated' => 'تم إلغاء تفعيل المستخدم بنجاح.',
        'password_updated' => 'تم تحديث كلمة المرور بنجاح.',
        'account_inactive' => 'حسابك غير نشط أو موقوف. يرجى التواصل مع المسؤول.',
        'confirm_delete' => 'هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.',
        'never_logged_in' => 'لم يسجّل دخولاً',
        'no_permissions' => 'لا توجد صلاحيات مباشرة.',
        'select_roles' => 'اختر دوراً واحداً أو أكثر لهذا المستخدم.',
    ],

    'placeholders' => [
        'name' => 'أدخل الاسم الكامل',
        'email' => 'user@organization.com',
        'mobile' => '+966 5XX XXX XXXX',
    ],

    'avatar' => [
        'choose' => 'اختيار صورة',
        'change' => 'تغيير الصورة',
        'remove' => 'إزالة الصورة الحالية',
        'hint' => 'JPG أو PNG أو WebP. الحد الأقصى 2 ميجابايت.',
    ],

    'show' => [
        'user_id' => 'معرّف المستخدم',
        'member_since' => 'عضو منذ',
        'days_member' => ':count يوم',
        'roles_assigned' => 'الأدوار المعيّنة',
        'permissions_granted' => 'الصلاحيات الممنوحة',
        'permission' => 'الصلاحية',
        'via_role' => 'عبر الدور',
        'coverage' => ':granted من :total',
        'contact_info' => 'معلومات التواصل',
        'account_timeline' => 'الجدول الزمني للحساب',
        'email_verified_at' => 'تم التوثيق في :date',
        'not_verified' => 'البريد غير موثّق',
        'permissions_count_label' => '{0} لا صلاحيات|{1} :count صلاحية|[2,*] :count صلاحيات',
    ],
];
