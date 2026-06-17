<?php

return [
    'title' => 'Transportation',
    'subtitle' => 'Campaign transportation trip management',
    'create_title' => 'Create Trip',
    'create_subtitle' => 'Schedule a new transportation trip',
    'edit_title' => 'Edit Trip',
    'show_title' => 'Trip Details',
    'passengers_title' => 'Passenger Management',

    'stats' => [
        'total' => 'Total Trips',
        'today' => 'Today\'s Trips',
        'upcoming' => 'Upcoming Trips',
        'completed' => 'Completed Trips',
        'patients_transported' => 'Patients Transported',
        'members_transported' => 'Members Transported',
        'passengers_transported' => 'Passengers Transported',
    ],

    'status' => [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'trip_type' => [
        'patient_transport' => 'Patient Transport',
        'member_transport' => 'Member Transport',
        'mixed_transport' => 'Mixed Transport',
    ],

    'passenger_type' => [
        'member' => 'Member',
        'patient' => 'Patient',
    ],

    'table' => [
        'trip_code' => 'Trip Code',
        'campaign' => 'Campaign',
        'trip_date' => 'Trip Date',
        'from' => 'From',
        'to' => 'To',
        'type' => 'Type',
        'passengers' => 'Passengers',
        'status' => 'Status',
        'created_by' => 'Created By',
        'actions' => 'Actions',
        'departure' => 'Departure',
        'arrival' => 'Arrival',
        'passenger' => 'Passenger',
        'passenger_type' => 'Type',
    ],

    'filters' => [
        'search' => 'Search',
        'search_placeholder' => 'Trip code, vehicle, driver…',
        'campaign' => 'Campaign',
        'all_campaigns' => 'All campaigns',
        'date_from' => 'Date From',
        'date_to' => 'Date To',
        'trip_type' => 'Trip Type',
        'all_types' => 'All types',
        'status' => 'Status',
        'all_statuses' => 'All statuses',
        'from_location' => 'From Location',
        'to_location' => 'To Location',
        'all_locations' => 'All locations',
        'apply' => 'Apply',
        'reset' => 'Reset',
    ],

    'fields' => [
        'campaign' => 'Campaign',
        'trip_date' => 'Trip Date',
        'departure_time' => 'Departure Time',
        'arrival_time' => 'Arrival Time',
        'from_location' => 'From Location',
        'to_location' => 'To Location',
        'trip_type' => 'Trip Type',
        'vehicle_number' => 'Vehicle Number',
        'driver_name' => 'Driver Name',
        'capacity' => 'Capacity',
        'notes' => 'Notes',
        'status' => 'Status',
        'passenger_type' => 'Passenger Type',
        'member' => 'Member',
        'patient' => 'Patient',
        'vehicle_info' => 'Vehicle Information',
        'trip_info' => 'Trip Information',
        'timeline' => 'Status Timeline',
    ],

    'actions' => [
        'create' => 'Create Trip',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'add_patient' => 'Add Patient',
        'add_member' => 'Add Member',
        'remove_passenger' => 'Remove',
        'start_trip' => 'Start Trip',
        'complete_trip' => 'Complete Trip',
        'cancel_trip' => 'Cancel Trip',
        'manage_passengers' => 'Manage Passengers',
    ],

    'messages' => [
        'created' => 'Transportation trip created successfully.',
        'updated' => 'Transportation trip updated successfully.',
        'deleted' => 'Transportation trip deleted successfully.',
        'status_changed' => 'Trip status updated successfully.',
        'passenger_added' => 'Passenger added successfully.',
        'passenger_removed' => 'Passenger removed successfully.',
        'trip_created' => 'Trip created with planned status.',
        'confirm_delete' => 'Are you sure you want to delete this trip?',
        'confirm_remove_passenger' => 'Remove this passenger from the trip?',
        'confirm_start' => 'Start this trip now?',
        'confirm_complete' => 'Mark this trip as completed?',
        'confirm_cancel' => 'Cancel this trip?',
        'no_passengers' => 'No passengers assigned to this trip yet.',
        'no_trips' => 'No transportation trips found.',
    ],

    'errors' => [
        'member_not_assigned' => 'Member is not assigned to the trip campaign.',
        'patient_not_in_campaign' => 'Patient does not belong to the trip campaign.',
        'passenger_already_on_trip' => 'This passenger is already on the trip.',
        'trip_not_editable' => 'This trip can no longer be edited.',
        'invalid_status_transition' => 'Invalid status transition.',
        'invalid_passenger_for_trip_type' => 'Passenger type is not allowed for this trip type.',
        'capacity_reached' => 'Trip capacity has been reached.',
        'cannot_delete_in_progress' => 'Cannot delete a trip that is in progress.',
    ],

    'campaign' => [
        'title' => 'Campaign Transportation',
        'recent_trips' => 'Recent Trips',
    ],

    'patient' => [
        'title' => 'Transportation History',
        'trip_history' => 'Trip History',
    ],

    'member' => [
        'title' => 'Transportation',
        'assigned_trips' => 'Assigned Trips',
        'upcoming' => 'Upcoming Trips',
    ],

    'locations' => [
        'add' => 'Add location',
        'add_title' => 'Add Transportation Location',
        'add_hint' => 'Add a new location if it is not listed.',
        'search_placeholder' => 'Search or select location...',
        'no_results' => 'No locations found',
        'searching' => 'Searching...',
        'type_to_search' => 'Type to search',
        'validation_required' => 'Name and type are required.',
    ],
];
