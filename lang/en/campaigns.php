<?php

return [
    'title' => 'Campaigns',
    'subtitle' => 'Manage medical charity campaigns',
    'add' => 'New Campaign',
    'create_title' => 'Create Campaign',
    'create_subtitle' => 'Define a new medical campaign',
    'edit_title' => 'Edit Campaign',
    'edit_subtitle' => 'Update campaign information',
    'show_title' => 'Campaign Details',
    'show_subtitle' => 'Campaign overview and statistics',

    'stats' => [
        'total' => 'Total Campaigns',
        'active' => 'Active Campaigns',
        'completed' => 'Completed Campaigns',
        'cancelled' => 'Cancelled Campaigns',
        'upcoming' => 'Upcoming Campaigns',
    ],

    'sections' => [
        'basic' => 'Basic Information',
        'details' => 'Campaign Details',
        'schedule' => 'Schedule',
        'expected' => 'Expected Results',
        'overview' => 'Campaign Overview',
        'future_stats' => 'Operational Statistics',
    ],

    'fields' => [
        'name' => 'Campaign Name',
        'code' => 'Campaign Code',
        'objective' => 'Objective',
        'target_group' => 'Target Group',
        'country' => 'Country',
        'city' => 'City',
        'specialty' => 'Medical Specialty',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'shifts_count' => 'Shifts Count',
        'expected_patients' => 'Expected Patients',
        'status' => 'Status',
        'description' => 'Description',
        'created_by' => 'Created By',
        'updated_by' => 'Updated By',
        'created_at' => 'Created Date',
        'updated_at' => 'Updated Date',
    ],

    'status' => [
        'draft' => 'Draft',
        'planned' => 'Planned',
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'table' => [
        'name' => 'Name',
        'country' => 'Country',
        'city' => 'City',
        'specialty' => 'Specialty',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'shifts' => 'Shifts',
        'expected_patients' => 'Expected Patients',
        'status' => 'Status',
        'created_by' => 'Created By',
        'actions' => 'Actions',
    ],

    'filters' => [
        'title' => 'Search & Filters',
        'search' => 'Search',
        'search_placeholder' => 'Search by name, objective, or target group...',
        'status' => 'Status',
        'country' => 'Country',
        'city' => 'City',
        'specialty' => 'Specialty',
        'start_from' => 'Start From',
        'end_to' => 'End To',
        'all_statuses' => 'All Statuses',
        'all_countries' => 'All Countries',
        'all_cities' => 'All Cities',
        'all_specialties' => 'All Specialties',
        'apply' => 'Apply Filters',
        'reset' => 'Reset',
    ],

    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'change_status' => 'Change Status',
    ],

    'future_stats' => [
        'patients' => 'Patients',
        'members' => 'Members',
        'attendance' => 'Attendance Records',
        'trips' => 'Transportation Trips',
        'activities' => 'Activities',
        'placeholder' => 'Coming soon',
    ],

    'tabs' => [
        'overview' => 'Overview',
        'team' => 'Campaign Team',
        'patients' => 'Patients',
        'attendance' => 'Attendance',
        'transportation' => 'Transportation',
        'activities' => 'Activities',
    ],

    'wizard' => [
        'title' => 'Campaign Setup Wizard',
        'subtitle' => 'Multi-step campaign configuration (coming soon)',
        'steps' => [
            'details' => 'Campaign Details',
            'members' => 'Assign Members',
            'patients_import' => 'Patients Import',
            'transportation' => 'Transportation Planning',
            'activities' => 'Activities Planning',
        ],
    ],

    'messages' => [
        'created' => 'Campaign created successfully.',
        'updated' => 'Campaign updated successfully.',
        'deleted' => 'Campaign deleted successfully.',
        'status_changed' => 'Campaign status updated successfully.',
        'confirm_delete' => 'Are you sure you want to delete this campaign? This action cannot be undone.',
        'empty' => 'No campaigns found.',
        'locked_completed' => 'Completed or cancelled campaigns require close permission to edit.',
    ],

    'placeholders' => [
        'name' => 'Enter campaign name',
        'objective' => 'Describe the campaign objective',
        'target_group' => 'e.g. Children, Elderly, Refugees',
        'description' => 'Additional notes or context',
        'select_country' => 'Select country',
        'select_city' => 'Select city',
        'select_specialty' => 'Select specialty',
    ],
];
