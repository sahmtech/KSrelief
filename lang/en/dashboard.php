<?php

return [
    'title' => 'Executive Dashboard',
    'subtitle' => 'Operational command center for medical campaign management',
    'campaign_title' => 'Campaign Dashboard',
    'new_campaign' => 'New Campaign',

    'subtitles' => [
        'doctor' => 'Clinical overview for patient care and medical workflow',
        'clinical' => 'Patient and workflow insights for your daily operations',
        'attendance_officer' => 'Attendance tracking and team presence overview',
        'reports_officer' => 'Reports and performance insights across campaigns',
        'operational' => 'Your personalized operational overview',
    ],

    'empty' => [
        'title' => 'No dashboard widgets available',
        'subtitle' => 'Your account does not currently have access to dashboard modules.',
    ],

    'filters' => [
        'campaign' => 'Campaign',
        'country' => 'Country',
        'city' => 'City',
        'specialty' => 'Specialty',
        'date_from' => 'From Date',
        'date_to' => 'To Date',
        'all' => 'All',
        'all_campaigns' => 'All Campaigns',
    ],

    'sections' => [
        'campaigns' => 'Campaign KPIs',
        'patients' => 'Patient KPIs',
        'workflow' => 'Medical Workflow KPIs',
        'members' => 'Member KPIs',
        'imports' => 'Import KPIs',
    ],

    'kpi' => [
        'total_campaigns' => 'Total Campaigns',
        'active_campaigns' => 'Active Campaigns',
        'completed_campaigns' => 'Completed Campaigns',
        'cancelled_campaigns' => 'Cancelled Campaigns',
        'total_patients' => 'Total Patients',
        'workflow_completion' => 'Workflow Completion',
        'accepted' => 'Accepted Patients',
        'rejected' => 'Rejected Patients',
        'postponed' => 'Postponed Patients',
        'admitted' => 'Admitted Patients',
        'completed_patients' => 'Completed Patients',
    ],

    'workflow' => [
        'progress' => 'Workflow Progress',
        'completed_cases' => 'Completed Cases',
        'patients_waiting' => 'Patients Waiting',
        'total_in_workflow' => 'Patients In Workflow',
        'patients_by_stage' => 'Patients By Stage',
        'waiting_admission' => 'Waiting For Admission',
        'waiting_operation' => 'Waiting For Operation',
        'waiting_activation' => 'Waiting For Activation',
        'in_rehabilitation' => 'In Rehabilitation',
        'completion_rate' => 'Workflow Completion Rate',
    ],

    'members' => [
        'total' => 'Total Members',
        'doctors' => 'Doctors',
        'specialists' => 'Specialists',
        'coordinators' => 'Coordinators',
        'assigned' => 'Assigned Members',
        'available' => 'Available Members',
    ],

    'attendance' => [
        'title' => 'Attendance KPIs',
        'present_today' => 'Present Today',
        'late_today' => 'Late Today',
        'absent_today' => 'Absent Today',
        'leave_today' => 'On Leave Today',
        'attendance_rate' => 'Today\'s Attendance Rate',
        'monthly_rate' => 'Monthly Attendance Rate',
        'recent_title' => 'Recent Attendance',
        'recent_subtitle' => 'Latest attendance records',
    ],

    'transportation' => [
        'title' => 'Transportation KPIs',
        'today_trips' => 'Today\'s Trips',
        'upcoming_trips' => 'Upcoming Trips',
        'completed_trips' => 'Completed Trips',
        'cancelled_trips' => 'Cancelled Trips',
        'patients_transported' => 'Patients Transported',
        'members_transported' => 'Members Transported',
        'passengers_today' => 'Passengers Today',
        'completed_today' => 'Completed Today',
        'recent_title' => 'Recent Trips',
        'recent_subtitle' => 'Latest transportation trips',
    ],

    'activities' => [
        'title' => 'Activity KPIs',
        'today' => 'Today\'s Activities',
        'upcoming' => 'Upcoming Activities',
        'completed_today' => 'Completed Today',
        'cancelled' => 'Cancelled Activities',
        'completion_rate' => 'Activity Completion Rate',
        'patients_participating' => 'Patients Participating',
        'members_participating' => 'Members Participating',
        'recent_title' => 'Upcoming Activities',
        'recent_subtitle' => 'Scheduled activities in the near term',
    ],

    'imports' => [
        'total' => 'Total Imports',
        'pending' => 'Pending Reviews',
        'completed' => 'Completed Imports',
        'failed' => 'Failed Imports',
        'patients_imported' => 'Patients Imported',
    ],

    'charts' => [
        'campaign_trend' => 'Campaign Trend',
        'campaigns' => 'Campaigns',
        'patients_by_stage' => 'Patients By Stage',
        'attendance_trend' => 'Attendance Trend',
        'attendance_rate' => 'Attendance Rate',
        'activities_trend' => 'Activities Trend',
        'transportation_trend' => 'Transportation Trend',
        'campaign_performance' => 'Campaign Performance Comparison',
        'patients' => 'Patients',
        'trips' => 'Trips',
        'no_data' => 'No data',
    ],

    'chart' => [
        'title' => 'Patient Registrations',
        'subtitle' => 'Patient registrations over the last 6 months',
        'series_name' => 'Patients',
    ],

    'recent_activity' => [
        'title' => 'Recent Activity',
        'subtitle' => 'Latest operational events across modules',
        'empty' => 'No recent activity found.',
    ],

    'upcoming' => [
        'title' => 'Upcoming Events',
        'subtitle' => 'Activities, trips, and campaigns ahead',
        'campaign' => 'Campaign start',
        'empty' => 'No upcoming events.',
    ],

    'quick_actions' => [
        'title' => 'Quick Actions',
        'subtitle' => 'Common operational tasks',
    ],

    'actions' => [
        'view_patients' => 'View Patients',
        'create_campaign' => 'Create Campaign',
        'add_patient' => 'Add Patient',
        'import_patients' => 'Import Patients',
        'create_activity' => 'Create Activity',
        'create_trip' => 'Create Trip',
        'record_attendance' => 'Record Attendance',
    ],

    'audit' => [
        'title' => 'Audit & System Health',
        'placeholder' => 'Audit logs, user activity tracking, and system health metrics will appear here.',
    ],

    'stats' => [
        'active_campaigns' => 'Active Campaigns',
        'total_patients' => 'Total Patients',
        'medical_staff' => 'Medical Staff',
        'pending_cases' => 'Patients Waiting',
    ],
];
