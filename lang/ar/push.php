<?php

return [
    'messages' => [
        'token_registered' => 'تم تسجيل الجهاز للإشعارات.',
        'token_removed' => 'تم إلغاء تسجيل الجهاز من الإشعارات.',
        'broadcast_queued' => 'تم إنشاء الإرسال وبدء التوصيل.',
    ],

    'errors' => [
        'no_recipients_selected' => 'حدّد مستلمين للإشعار.',
    ],

    'titles' => [
        'patient_created' => 'مريض جديد',
        'patient_stage_changed' => 'تحديث مسار العلاج',
        'medical_record_created' => 'سجل طبي جديد',
        'activity_created' => 'نشاط جديد',
        'activity_status_changed' => 'تحديث نشاط',
        'activity_participant_added' => 'مشارك في نشاط',
        'transportation_passenger_added' => 'نقل',
        'transportation_status_changed' => 'تحديث رحلة',
        'patient_import_approved' => 'استيراد مرضى',
    ],

    'bodies' => [
        'patient_created' => 'تم تسجيل :name (:campaign).',
        'patient_stage_changed' => ':name انتقل إلى :stage.',
        'medical_record_created' => 'سجل جديد لـ :name (:stage).',
        'activity_created' => ':title (:type).',
        'activity_status_changed' => ':title — :status.',
        'activity_participant_added' => ':name انضم إلى :activity.',
        'transportation_passenger_added' => 'تمت إضافة :patient إلى الرحلة :trip.',
        'transportation_status_changed' => 'الرحلة :trip — :status.',
        'patient_import_approved' => 'تم استيراد :count مريض إلى :campaign.',
    ],
];
