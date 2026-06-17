<?php

return [
    'title' => 'Users',
    'subtitle' => 'System user management',
    'add' => 'Add User',
    'create_title' => 'Create User',
    'create_subtitle' => 'Add a new system user and assign roles',
    'edit_title' => 'Edit User',
    'edit_subtitle' => 'Update user profile and role assignments',
    'show_title' => 'User Details',
    'show_subtitle' => 'View account information and permissions',

    'fields' => [
        'name' => 'Full Name',
        'email' => 'Email Address',
        'mobile' => 'Mobile Number',
        'gender' => 'Gender',
        'status' => 'Status',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'roles' => 'Roles',
        'last_login' => 'Last Login',
        'created_at' => 'Created Date',
        'updated_at' => 'Updated Date',
        'email_verified' => 'Email Verified',
        'avatar' => 'Profile Photo',
    ],

    'gender' => [
        'male' => 'Male',
        'female' => 'Female',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ],

    'sections' => [
        'personal' => 'Personal Information',
        'account' => 'Account Status',
        'roles' => 'Assigned Roles',
        'permissions' => 'Granted Permissions',
        'photo' => 'Profile Photo',
    ],

    'table' => [
        'name' => 'Name',
        'email' => 'Email',
        'mobile' => 'Mobile',
        'role' => 'Role',
        'status' => 'Status',
        'last_login' => 'Last Login',
        'created_at' => 'Created',
        'actions' => 'Actions',
    ],

    'filters' => [
        'title' => 'Filters',
        'role' => 'Role',
        'status' => 'Status',
        'all_roles' => 'All Roles',
        'all_statuses' => 'All Statuses',
        'apply' => 'Apply Filters',
        'reset' => 'Reset',
    ],

    'actions' => [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'change_password' => 'Change Password',
    ],

    'change_password' => [
        'title' => 'Change Password',
        'subtitle' => 'Set a new password for this user',
        'submit' => 'Update Password',
    ],

    'messages' => [
        'created' => 'User created successfully.',
        'updated' => 'User updated successfully.',
        'deleted' => 'User deleted successfully.',
        'activated' => 'User activated successfully.',
        'deactivated' => 'User deactivated successfully.',
        'password_updated' => 'Password updated successfully.',
        'account_inactive' => 'Your account is inactive or suspended. Please contact the administrator.',
        'confirm_delete' => 'Are you sure you want to delete this user? This action cannot be undone.',
        'never_logged_in' => 'Never',
        'no_permissions' => 'No direct permissions granted.',
        'select_roles' => 'Select one or more roles for this user.',
    ],

    'placeholders' => [
        'name' => 'Enter full name',
        'email' => 'user@organization.com',
        'mobile' => '+966 5XX XXX XXXX',
    ],

    'avatar' => [
        'choose' => 'Choose Photo',
        'change' => 'Change Photo',
        'remove' => 'Remove current photo',
        'hint' => 'JPG, PNG or WebP. Max 2 MB.',
    ],

    'show' => [
        'user_id' => 'User ID',
        'member_since' => 'Member Since',
        'days_member' => ':count days',
        'roles_assigned' => 'Roles Assigned',
        'permissions_granted' => 'Permissions Granted',
        'permission' => 'Permission',
        'via_role' => 'Via Role',
        'coverage' => ':granted of :total',
        'contact_info' => 'Contact Information',
        'account_timeline' => 'Account Timeline',
        'email_verified_at' => 'Verified on :date',
        'not_verified' => 'Email not verified',
        'permissions_count_label' => '{0} No permissions|{1} :count permission|[2,*] :count permissions',
    ],
];
