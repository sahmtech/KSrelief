<?php

return [
    'dashboard' => [
        'title' => 'Master Settings',
        'subtitle' => 'Manage reference data and system lookup values',
        'total' => 'Total',
        'active' => 'Active',
        'quick_access' => 'Manage',
        'cards' => [
            'countries' => 'Countries',
            'cities' => 'Cities',
            'specialties' => 'Specialties',
            'member_roles' => 'Member Roles',
            'patient_eligibility_statuses' => 'Patient Eligibility Statuses',
            'patient_stages' => 'Patient Stages',
            'activity_types' => 'Activity Types',
            'transportation_locations' => 'Transportation Locations',
            'attendance_statuses' => 'Attendance Statuses',
            'campaign_statuses' => 'Campaign Statuses',
            'implant_companies' => 'Implant Companies',
            'insertion_approaches' => 'Insertion Approaches',
            'ct_finding_options' => 'CT Finding Options',
            'mri_finding_options' => 'MRI Finding Options',
            'expectation_post_ci_options' => 'Expectations Post CI Options',
        ],
    ],

    'debug' => [
        'title' => 'Debug Tools',
        'backfill_description' => 'Visible only when APP_DEBUG=true. Generates missing campaign and patient codes for existing records.',
        'missing_campaign_codes' => 'Campaigns without code',
        'missing_patient_codes' => 'Patients without code',
        'backfill_action' => 'Generate Missing Codes',
        'backfill_confirm' => 'Generate codes for all campaigns and patients that are missing them?',
        'backfill_success' => 'Generated :campaigns campaign code(s) and :patients patient code(s).',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'fields' => [
        'name' => 'Name',
        'name_ar' => 'Arabic Name',
        'code' => 'Code',
        'iso2' => 'ISO2',
        'iso3' => 'ISO3',
        'phone_code' => 'Phone Code',
        'country' => 'Country',
        'description' => 'Description',
        'color' => 'Color',
        'sort_order' => 'Sort Order',
        'is_default' => 'Default',
        'type' => 'Type',
        'status' => 'Status',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'created_by' => 'Created By',
        'updated_by' => 'Updated By',
    ],

    'filters' => [
        'title' => 'Filters',
        'search' => 'Search',
        'search_placeholder' => 'Search by name or code...',
        'status' => 'Status',
        'country' => 'Country',
        'type' => 'Type',
        'all_statuses' => 'All Statuses',
        'all_countries' => 'All Countries',
        'all_types' => 'All Types',
        'apply' => 'Apply Filters',
        'reset' => 'Reset',
    ],

    'table' => [
        'name' => 'Name',
        'code' => 'Code',
        'country' => 'Country',
        'description' => 'Description',
        'color' => 'Color',
        'sort_order' => 'Order',
        'is_default' => 'Default',
        'type' => 'Type',
        'status' => 'Status',
        'iso2' => 'ISO2',
        'iso3' => 'ISO3',
        'phone_code' => 'Phone Code',
        'actions' => 'Actions',
    ],

    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'create' => 'Create',
    ],

    'messages' => [
        'empty' => 'No records found.',
        'confirm_delete' => 'Are you sure you want to delete this record?',
        'yes' => 'Yes',
        'no' => 'No',
    ],

    'sections' => [
        'details' => 'Details',
        'audit' => 'Audit Information',
    ],

    'transportation_types' => [
        'hotel' => 'Hotel',
        'hospital' => 'Hospital',
        'airport' => 'Airport',
        'other' => 'Other',
    ],

    'entities' => [
        'countries' => [
            'messages' => [
                'created' => 'Country created successfully.',
                'updated' => 'Country updated successfully.',
                'deleted' => 'Country deleted successfully.',
            ],
        ],
        'cities' => [
            'messages' => [
                'created' => 'City created successfully.',
                'updated' => 'City updated successfully.',
                'deleted' => 'City deleted successfully.',
            ],
        ],
        'specialties' => [
            'messages' => [
                'created' => 'Specialty created successfully.',
                'updated' => 'Specialty updated successfully.',
                'deleted' => 'Specialty deleted successfully.',
            ],
        ],
        'member_roles' => [
            'messages' => [
                'created' => 'Member role created successfully.',
                'updated' => 'Member role updated successfully.',
                'deleted' => 'Member role deleted successfully.',
            ],
        ],
        'patient_eligibility_statuses' => [
            'messages' => [
                'created' => 'Patient eligibility status created successfully.',
                'updated' => 'Patient eligibility status updated successfully.',
                'deleted' => 'Patient eligibility status deleted successfully.',
            ],
        ],
        'patient_stages' => [
            'messages' => [
                'created' => 'Patient stage created successfully.',
                'updated' => 'Patient stage updated successfully.',
                'deleted' => 'Patient stage deleted successfully.',
            ],
        ],
        'activity_types' => [
            'messages' => [
                'created' => 'Activity type created successfully.',
                'updated' => 'Activity type updated successfully.',
                'deleted' => 'Activity type deleted successfully.',
            ],
        ],
        'transportation_locations' => [
            'messages' => [
                'created' => 'Transportation location created successfully.',
                'updated' => 'Transportation location updated successfully.',
                'deleted' => 'Transportation location deleted successfully.',
            ],
        ],
        'attendance_statuses' => [
            'messages' => [
                'created' => 'Attendance status created successfully.',
                'updated' => 'Attendance status updated successfully.',
                'deleted' => 'Attendance status deleted successfully.',
            ],
        ],
        'campaign_statuses' => [
            'messages' => [
                'created' => 'Campaign status created successfully.',
                'updated' => 'Campaign status updated successfully.',
                'deleted' => 'Campaign status deleted successfully.',
            ],
        ],
        'implant_companies' => [
            'messages' => [
                'created' => 'Implant company created successfully.',
                'updated' => 'Implant company updated successfully.',
                'deleted' => 'Implant company deleted successfully.',
            ],
        ],
        'insertion_approaches' => [
            'messages' => [
                'created' => 'Insertion approach created successfully.',
                'updated' => 'Insertion approach updated successfully.',
                'deleted' => 'Insertion approach deleted successfully.',
            ],
        ],
        'ct_finding_options' => [
            'messages' => [
                'created' => 'CT finding option created successfully.',
                'updated' => 'CT finding option updated successfully.',
                'deleted' => 'CT finding option deleted successfully.',
            ],
        ],
        'mri_finding_options' => [
            'messages' => [
                'created' => 'MRI finding option created successfully.',
                'updated' => 'MRI finding option updated successfully.',
                'deleted' => 'MRI finding option deleted successfully.',
            ],
        ],
        'expectation_post_ci_options' => [
            'messages' => [
                'created' => 'Expectations Post CI option created successfully.',
                'updated' => 'Expectations Post CI option updated successfully.',
                'deleted' => 'Expectations Post CI option deleted successfully.',
            ],
        ],
    ],

    'countries' => [
        'title' => 'Countries',
        'subtitle' => 'Manage country reference data',
        'singular' => 'Country',
        'add' => 'Add Country',
        'create_title' => 'Create Country',
        'create_subtitle' => 'Add a new country record',
        'edit_title' => 'Edit Country',
        'edit_subtitle' => 'Update country information',
        'show_title' => 'Country Details',
        'show_subtitle' => 'View country information',
    ],

    'cities' => [
        'title' => 'Cities',
        'subtitle' => 'Manage city reference data',
        'singular' => 'City',
        'add' => 'Add City',
        'create_title' => 'Create City',
        'create_subtitle' => 'Add a new city record',
        'edit_title' => 'Edit City',
        'edit_subtitle' => 'Update city information',
        'show_title' => 'City Details',
        'show_subtitle' => 'View city information',
    ],

    'specialties' => [
        'title' => 'Specialties',
        'subtitle' => 'Manage medical specialty reference data',
        'singular' => 'Specialty',
        'add' => 'Add Specialty',
        'create_title' => 'Create Specialty',
        'create_subtitle' => 'Add a new specialty record',
        'edit_title' => 'Edit Specialty',
        'edit_subtitle' => 'Update specialty information',
        'show_title' => 'Specialty Details',
        'show_subtitle' => 'View specialty information',
    ],

    'member_roles' => [
        'title' => 'Member Roles',
        'subtitle' => 'Manage medical staff role reference data',
        'singular' => 'Member Role',
        'add' => 'Add Member Role',
        'create_title' => 'Create Member Role',
        'create_subtitle' => 'Add a new member role record',
        'edit_title' => 'Edit Member Role',
        'edit_subtitle' => 'Update member role information',
        'show_title' => 'Member Role Details',
        'show_subtitle' => 'View member role information',
    ],

    'patient_eligibility_statuses' => [
        'title' => 'Patient Eligibility Statuses',
        'subtitle' => 'Manage patient eligibility status reference data',
        'singular' => 'Patient Eligibility Status',
        'add' => 'Add Status',
        'create_title' => 'Create Patient Eligibility Status',
        'create_subtitle' => 'Add a new patient eligibility status',
        'edit_title' => 'Edit Patient Eligibility Status',
        'edit_subtitle' => 'Update patient eligibility status information',
        'show_title' => 'Patient Eligibility Status Details',
        'show_subtitle' => 'View patient eligibility status information',
    ],

    'patient_stages' => [
        'title' => 'Patient Stages',
        'subtitle' => 'Manage patient stage reference data',
        'singular' => 'Patient Stage',
        'add' => 'Add Patient Stage',
        'create_title' => 'Create Patient Stage',
        'create_subtitle' => 'Add a new patient stage record',
        'edit_title' => 'Edit Patient Stage',
        'edit_subtitle' => 'Update patient stage information',
        'show_title' => 'Patient Stage Details',
        'show_subtitle' => 'View patient stage information',
    ],

    'activity_types' => [
        'title' => 'Activity Types',
        'subtitle' => 'Manage activity type reference data',
        'singular' => 'Activity Type',
        'add' => 'Add Activity Type',
        'create_title' => 'Create Activity Type',
        'create_subtitle' => 'Add a new activity type record',
        'edit_title' => 'Edit Activity Type',
        'edit_subtitle' => 'Update activity type information',
        'show_title' => 'Activity Type Details',
        'show_subtitle' => 'View activity type information',
    ],

    'transportation_locations' => [
        'title' => 'Transportation Locations',
        'subtitle' => 'Manage transportation location reference data',
        'singular' => 'Transportation Location',
        'add' => 'Add Location',
        'create_title' => 'Create Transportation Location',
        'create_subtitle' => 'Add a new transportation location',
        'edit_title' => 'Edit Transportation Location',
        'edit_subtitle' => 'Update transportation location information',
        'show_title' => 'Transportation Location Details',
        'show_subtitle' => 'View transportation location information',
    ],

    'attendance_statuses' => [
        'title' => 'Attendance Statuses',
        'subtitle' => 'Manage attendance status reference data',
        'singular' => 'Attendance Status',
        'add' => 'Add Attendance Status',
        'create_title' => 'Create Attendance Status',
        'create_subtitle' => 'Add a new attendance status record',
        'edit_title' => 'Edit Attendance Status',
        'edit_subtitle' => 'Update attendance status information',
        'show_title' => 'Attendance Status Details',
        'show_subtitle' => 'View attendance status information',
    ],

    'campaign_statuses' => [
        'title' => 'Campaign Statuses',
        'subtitle' => 'Manage campaign status reference data',
        'singular' => 'Campaign Status',
        'add' => 'Add Campaign Status',
        'create_title' => 'Create Campaign Status',
        'create_subtitle' => 'Add a new campaign status record',
        'edit_title' => 'Edit Campaign Status',
        'edit_subtitle' => 'Update campaign status information',
        'show_title' => 'Campaign Status Details',
        'show_subtitle' => 'View campaign status information',
    ],

    'implant_companies' => [
        'title' => 'Implant Companies',
        'subtitle' => 'Manage cochlear implant companies and electrode types',
        'singular' => 'Implant Company',
        'add' => 'Add Company',
        'create_title' => 'Create Implant Company',
        'create_subtitle' => 'Add a company with its electrode types',
        'edit_title' => 'Edit Implant Company',
        'edit_subtitle' => 'Update company details and electrode types',
        'show_title' => 'Implant Company Details',
        'show_subtitle' => 'View company and electrode type list',
        'electrode_types' => 'Electrode Types',
        'add_electrode' => 'Add Electrode Type',
        'electrode_types_hint' => 'Each electrode type appears in the Operation stage when this company is selected.',
        'electrode_name_placeholder' => 'Electrode type name',
    ],

    'insertion_approaches' => [
        'title' => 'Insertion Approaches',
        'subtitle' => 'Manage insertion approach options for the Operation stage',
        'singular' => 'Insertion Approach',
        'add' => 'Add Approach',
        'create_title' => 'Create Insertion Approach',
        'create_subtitle' => 'Add a new insertion approach option',
        'edit_title' => 'Edit Insertion Approach',
        'edit_subtitle' => 'Update insertion approach information',
        'show_title' => 'Insertion Approach Details',
        'show_subtitle' => 'View insertion approach information',
    ],

    'ct_finding_options' => [
        'title' => 'CT Finding Options',
        'subtitle' => 'Manage CT finding choices for imaging screening',
        'singular' => 'CT Finding Option',
        'add' => 'Add CT Finding',
        'create_title' => 'Create CT Finding Option',
        'create_subtitle' => 'Add a new CT finding option',
        'edit_title' => 'Edit CT Finding Option',
        'edit_subtitle' => 'Update CT finding option information',
        'show_title' => 'CT Finding Option Details',
        'show_subtitle' => 'View CT finding option information',
    ],

    'mri_finding_options' => [
        'title' => 'MRI Finding Options',
        'subtitle' => 'Manage MRI finding choices for imaging screening',
        'singular' => 'MRI Finding Option',
        'add' => 'Add MRI Finding',
        'create_title' => 'Create MRI Finding Option',
        'create_subtitle' => 'Add a new MRI finding option',
        'edit_title' => 'Edit MRI Finding Option',
        'edit_subtitle' => 'Update MRI finding option information',
        'show_title' => 'MRI Finding Option Details',
        'show_subtitle' => 'View MRI finding option information',
    ],

    'expectation_post_ci_options' => [
        'title' => 'Expectations Post CI Options',
        'subtitle' => 'Manage Expectations Post CI choices for patient screening',
        'singular' => 'Expectations Post CI Option',
        'add' => 'Add Option',
        'create_title' => 'Create Expectations Post CI Option',
        'create_subtitle' => 'Add a new Expectations Post CI option',
        'edit_title' => 'Edit Expectations Post CI Option',
        'edit_subtitle' => 'Update Expectations Post CI option information',
        'show_title' => 'Expectations Post CI Option Details',
        'show_subtitle' => 'View Expectations Post CI option information',
    ],
];
