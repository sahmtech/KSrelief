<?php

return [
    'title' => 'Attendance',
    'subtitle' => 'Campaign personnel attendance management',
    'quick_title' => 'Quick Attendance',
    'quick_subtitle' => 'Record attendance for all campaign members',
    'grid_title' => 'Team Attendance Grid',
    'create_title' => 'Record Attendance',
    'create_subtitle' => 'Check in a campaign member',
    'edit_title' => 'Edit Attendance',
    'show_title' => 'Attendance Record',

    'stats' => [
        'total' => 'Total Records',
        'present_today' => 'Present Today',
        'late_today' => 'Late Today',
        'absent_today' => 'Absent Today',
        'leave_today' => 'Leave Today',
        'attendance_rate' => 'Attendance Rate',
        'present' => 'Present',
        'late' => 'Late',
        'absent' => 'Absent',
        'leave' => 'Leave',
    ],

    'table' => [
        'date' => 'Date',
        'campaign' => 'Campaign',
        'member' => 'Member',
        'role' => 'Role',
        'specialty' => 'Specialty',
        'shift' => 'Shift',
        'check_in' => 'Check In',
        'check_out' => 'Check Out',
        'worked_hours' => 'Worked Hours',
        'status' => 'Status',
        'recorded_by' => 'Recorded By',
        'actions' => 'Actions',
    ],

    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'Member name, mobile, email…',
        'campaign' => 'Campaign',
        'all_campaigns' => 'All campaigns',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'shift' => 'Shift',
        'all_shifts' => 'All shifts',
        'status' => 'Status',
        'all_statuses' => 'All statuses',
        'role' => 'Role',
        'all_roles' => 'All roles',
        'specialty' => 'Specialty',
        'all_specialties' => 'All specialties',
        'apply' => 'Apply',
        'reset' => 'Reset',
    ],

    'fields' => [
        'campaign' => 'Campaign',
        'member' => 'Member',
        'date' => 'Date',
        'shift' => 'Shift',
        'check_in' => 'Check In',
        'check_out' => 'Check Out',
        'status' => 'Attendance Status',
        'notes' => 'Notes',
        'worked_hours' => 'Worked Hours',
        'recorded_by' => 'Recorded By',
    ],

    'actions' => [
        'record' => 'Record Attendance',
        'quick' => 'Quick Attendance',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save_all' => 'Save All',
        'load_members' => 'Load Members',
    ],

    'messages' => [
        'created' => 'Attendance recorded successfully.',
        'updated' => 'Attendance updated successfully.',
        'deleted' => 'Attendance deleted successfully.',
        'bulk_saved' => ':created created, :updated updated.',
        'confirm_delete' => 'Are you sure you want to delete this attendance record?',
        'no_members' => 'No members assigned to this campaign.',
        'select_campaign' => 'Select a campaign to load members.',
    ],

    'errors' => [
        'member_not_assigned' => 'Member is not assigned to the selected campaign.',
        'duplicate_record' => 'Attendance already exists for this member, date, and shift.',
    ],

    'member' => [
        'summary' => 'Attendance Summary',
        'recent' => 'Recent Attendance',
        'rate' => 'Attendance Rate',
    ],

    'campaign' => [
        'title' => 'Campaign Attendance',
        'daily_list' => 'Daily Attendance',
        'quick_link' => 'Open Quick Attendance',
    ],
];
