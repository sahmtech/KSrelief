<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PatientAttachmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientImportController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\PatientWorkflowController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityParticipantController;
use App\Http\Controllers\TransportationPassengerController;
use App\Http\Controllers\TransportationLocationLookupController;
use App\Http\Controllers\TransportationTripController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(app(\App\Support\DashboardAccessResolver::class)->defaultRoute(auth()->user()));
});

Route::get('locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Admin Panel — protected by auth + permission middleware
Route::middleware('auth')->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('can:accessDashboard')
        ->name('dashboard');
    Route::get('dashboard/api', [DashboardController::class, 'api'])
        ->middleware('can:accessDashboard')
        ->name('dashboard.api');

    // Campaign Management
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])
            ->middleware('permission:campaign.view')
            ->name('index');
        Route::get('create', [CampaignController::class, 'create'])
            ->middleware('permission:campaign.create')
            ->name('create');
        Route::post('/', [CampaignController::class, 'store'])
            ->middleware('permission:campaign.create')
            ->name('store');
        Route::get('{campaign}/dashboard', [DashboardController::class, 'campaignDashboard'])
            ->middleware('permission:campaign_dashboard.view')
            ->name('dashboard');
        Route::get('{campaign}', [CampaignController::class, 'show'])
            ->middleware('permission:campaign.view')
            ->name('show');
        Route::get('{campaign}/edit', [CampaignController::class, 'edit'])
            ->middleware('role_or_permission:campaign.update|campaign.close')
            ->name('edit');
        Route::put('{campaign}', [CampaignController::class, 'update'])
            ->middleware('role_or_permission:campaign.update|campaign.close')
            ->name('update');
        Route::delete('{campaign}', [CampaignController::class, 'destroy'])
            ->middleware('permission:campaign.delete')
            ->name('destroy');
        Route::patch('{campaign}/status', [CampaignController::class, 'changeStatus'])
            ->middleware('role_or_permission:campaign.close|campaign.update')
            ->name('status.update');
        Route::post('{campaign}/team', [CampaignController::class, 'assignMember'])
            ->middleware('permission:member.assign_campaign')
            ->name('team.assign');
        Route::delete('{campaign}/team/{member}', [CampaignController::class, 'removeMember'])
            ->middleware('permission:member.assign_campaign')
            ->name('team.remove');
    });

    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('countries', [\App\Http\Controllers\Administration\LocationController::class, 'countries'])
            ->middleware('permission:campaign.view')
            ->name('countries');
        Route::get('countries/{country}/cities', [\App\Http\Controllers\Administration\LocationController::class, 'cities'])
            ->middleware('permission:campaign.view')
            ->name('cities');
        Route::post('countries/{country}/cities', [\App\Http\Controllers\Administration\LocationController::class, 'storeCity'])
            ->middleware('role_or_permission:campaign.create|campaign.update|settings.update')
            ->name('cities.store');
    });

    Route::prefix('specialties')->name('specialties.')->group(function () {
        Route::get('/', [SpecialtyController::class, 'index'])
            ->middleware('permission:campaign.view')
            ->name('index');
        Route::post('/', [SpecialtyController::class, 'store'])
            ->middleware('role_or_permission:campaign.create|campaign.update|settings.update')
            ->name('store');
    });

    // Medical Staff
    Route::prefix('medical-staff')->name('medical-staff.')->group(function () {
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])
                ->middleware('permission:member.view')
                ->name('index');
            Route::get('create', [MemberController::class, 'create'])
                ->middleware('permission:member.create')
                ->name('create');
            Route::post('/', [MemberController::class, 'store'])
                ->middleware('permission:member.create')
                ->name('store');
            Route::get('import', [MemberController::class, 'importForm'])
                ->middleware('permission:member.import_excel')
                ->name('import');
            Route::get('import/template', [MemberController::class, 'downloadTemplate'])
                ->middleware('permission:member.import_excel')
                ->name('import.template');
            Route::post('import', [MemberController::class, 'import'])
                ->middleware('permission:member.import_excel')
                ->name('import.store');
            Route::get('{member}', [MemberController::class, 'show'])
                ->middleware('permission:member.view')
                ->name('show');
            Route::get('{member}/edit', [MemberController::class, 'edit'])
                ->middleware('permission:member.update')
                ->name('edit');
            Route::put('{member}', [MemberController::class, 'update'])
                ->middleware('permission:member.update')
                ->name('update');
            Route::delete('{member}', [MemberController::class, 'destroy'])
                ->middleware('permission:member.delete')
                ->name('destroy');
            Route::get('{member}/campaigns', [MemberController::class, 'campaigns'])
                ->middleware('permission:member.assign_campaign')
                ->name('campaigns');
            Route::post('{member}/campaigns', [MemberController::class, 'assignCampaign'])
                ->middleware('permission:member.assign_campaign')
                ->name('campaigns.assign');
            Route::delete('{member}/campaigns/{campaign}', [MemberController::class, 'removeCampaign'])
                ->middleware('permission:member.assign_campaign')
                ->name('campaigns.remove');
        });
    });

    // Patients
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])
            ->middleware('permission:patient.view')
            ->name('index');
        Route::get('create', [PatientController::class, 'create'])
            ->middleware('permission:patient.create')
            ->name('create');
        Route::post('/', [PatientController::class, 'store'])
            ->middleware('permission:patient.create')
            ->name('store');

        // Import — must be before {patient} wildcard
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('/', [PatientImportController::class, 'index'])
                ->middleware('permission:patient.import_history')
                ->name('index');
            Route::get('create', [PatientImportController::class, 'create'])
                ->middleware('permission:patient.import_excel')
                ->name('create');
            Route::post('/', [PatientImportController::class, 'store'])
                ->middleware('permission:patient.import_excel')
                ->name('store');
            Route::get('template', [PatientImportController::class, 'downloadTemplate'])
                ->middleware('permission:patient.import_excel')
                ->name('template');
            Route::get('{batch}', [PatientImportController::class, 'show'])
                ->middleware('permission:patient.import_history')
                ->name('show');
            Route::post('{batch}/approve', [PatientImportController::class, 'approve'])
                ->middleware('permission:patient.import_approve')
                ->name('approve');
            Route::get('{batch}/errors', [PatientImportController::class, 'downloadErrors'])
                ->middleware('permission:patient.import_history')
                ->name('errors');
        });

        // Workflow — must be before {patient} wildcard
        Route::prefix('{patient}/workflow')->name('workflow.')->group(function () {
            Route::get('/', [PatientWorkflowController::class, 'timeline'])
                ->middleware('permission:stage.view')
                ->name('timeline');
            Route::post('/stage', [PatientWorkflowController::class, 'changeStage'])
                ->middleware('permission:stage.change')
                ->name('change-stage');
            Route::get('/history', [PatientWorkflowController::class, 'history'])
                ->middleware('permission:stage.history.view')
                ->name('history');
        });

        // Medical Records — must be before {patient} wildcard
        Route::prefix('{patient}/records')->name('records.')->group(function () {
            Route::get('/', [MedicalRecordController::class, 'index'])
                ->middleware('permission:medical_record.view')
                ->name('index');
            Route::get('create', [MedicalRecordController::class, 'create'])
                ->middleware('permission:medical_record.create')
                ->name('create');
            Route::post('/', [MedicalRecordController::class, 'store'])
                ->middleware('permission:medical_record.create')
                ->name('store');
            Route::get('stage-fields', [MedicalRecordController::class, 'stageFields'])
                ->middleware('permission:medical_record.create')
                ->name('stage-fields');
            Route::get('{record}', [MedicalRecordController::class, 'show'])
                ->middleware('permission:medical_record.view')
                ->name('show');
            Route::get('{record}/edit', [MedicalRecordController::class, 'edit'])
                ->middleware('permission:medical_record.update')
                ->name('edit');
            Route::put('{record}', [MedicalRecordController::class, 'update'])
                ->middleware('permission:medical_record.update')
                ->name('update');
            Route::delete('{record}', [MedicalRecordController::class, 'destroy'])
                ->middleware('permission:medical_record.delete')
                ->name('destroy');
        });

        // Individual patient routes — after all static segments
        Route::get('{patient}', [PatientController::class, 'show'])
            ->middleware('permission:patient.view')
            ->name('show');
        Route::get('{patient}/edit', [PatientController::class, 'edit'])
            ->middleware('permission:patient.update')
            ->name('edit');
        Route::put('{patient}', [PatientController::class, 'update'])
            ->middleware('permission:patient.update')
            ->name('update');
        Route::delete('{patient}', [PatientController::class, 'destroy'])
            ->middleware('permission:patient.delete')
            ->name('destroy');
        Route::post('{patient}/attachments', [PatientAttachmentController::class, 'store'])
            ->middleware('permission:patient.update')
            ->name('attachments.store');
        Route::get('{patient}/attachments/{attachment}/download', [PatientAttachmentController::class, 'download'])
            ->middleware('permission:patient.view')
            ->name('attachments.download');
        Route::delete('{patient}/attachments/{attachment}', [PatientAttachmentController::class, 'destroy'])
            ->middleware('permission:patient.update')
            ->name('attachments.destroy');
    });

    // Operations
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])
                ->middleware('permission:attendance.view')
                ->name('index');
            Route::get('quick', [AttendanceController::class, 'quickAttendance'])
                ->middleware('permission:attendance.create')
                ->name('quick');
            Route::post('bulk', [AttendanceController::class, 'bulkStore'])
                ->middleware('permission:attendance.create')
                ->name('bulk');
            Route::get('create', [AttendanceController::class, 'create'])
                ->middleware('permission:attendance.create')
                ->name('create');
            Route::post('/', [AttendanceController::class, 'store'])
                ->middleware('permission:attendance.create')
                ->name('store');
            Route::get('{attendance}', [AttendanceController::class, 'show'])
                ->middleware('permission:attendance.view')
                ->name('show');
            Route::get('{attendance}/edit', [AttendanceController::class, 'edit'])
                ->middleware('permission:attendance.update')
                ->name('edit');
            Route::put('{attendance}', [AttendanceController::class, 'update'])
                ->middleware('permission:attendance.update')
                ->name('update');
            Route::delete('{attendance}', [AttendanceController::class, 'destroy'])
                ->middleware('permission:attendance.delete')
                ->name('destroy');
        });

        Route::prefix('transportation')->name('transportation.')->group(function () {
            Route::get('/', [TransportationTripController::class, 'index'])
                ->middleware('permission:transportation.view')
                ->name('index');
            Route::get('locations/search', [TransportationLocationLookupController::class, 'search'])
                ->middleware('permission:transportation.view')
                ->name('locations.search');
            Route::post('locations', [TransportationLocationLookupController::class, 'store'])
                ->middleware('role_or_permission:transportation.create|transport_location.create')
                ->name('locations.store');
            Route::get('create', [TransportationTripController::class, 'create'])
                ->middleware('permission:transportation.create')
                ->name('create');
            Route::post('/', [TransportationTripController::class, 'store'])
                ->middleware('permission:transportation.create')
                ->name('store');
            Route::get('{trip}', [TransportationTripController::class, 'show'])
                ->middleware('permission:transportation.view')
                ->name('show');
            Route::get('{trip}/edit', [TransportationTripController::class, 'edit'])
                ->middleware('permission:transportation.update')
                ->name('edit');
            Route::put('{trip}', [TransportationTripController::class, 'update'])
                ->middleware('permission:transportation.update')
                ->name('update');
            Route::delete('{trip}', [TransportationTripController::class, 'destroy'])
                ->middleware('permission:transportation.delete')
                ->name('destroy');
            Route::patch('{trip}/status', [TransportationTripController::class, 'changeStatus'])
                ->middleware('permission:transportation.change_status')
                ->name('status.update');
            Route::post('{trip}/passengers', [TransportationPassengerController::class, 'store'])
                ->middleware('permission:transportation.manage_passengers')
                ->name('passengers.store');
            Route::delete('{trip}/passengers/{passenger}', [TransportationPassengerController::class, 'destroy'])
                ->middleware('permission:transportation.manage_passengers')
                ->name('passengers.destroy');
        });

        Route::prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])
                ->middleware('permission:activity.view')
                ->name('index');
            Route::get('calendar', [ActivityController::class, 'calendar'])
                ->middleware('permission:activity.view')
                ->name('calendar');
            Route::get('calendar/events', [ActivityController::class, 'calendarEvents'])
                ->middleware('permission:activity.view')
                ->name('calendar.events');
            Route::get('create', [ActivityController::class, 'create'])
                ->middleware('permission:activity.create')
                ->name('create');
            Route::post('/', [ActivityController::class, 'store'])
                ->middleware('permission:activity.create')
                ->name('store');
            Route::get('{activity}', [ActivityController::class, 'show'])
                ->middleware('permission:activity.view')
                ->name('show');
            Route::get('{activity}/edit', [ActivityController::class, 'edit'])
                ->middleware('permission:activity.update')
                ->name('edit');
            Route::put('{activity}', [ActivityController::class, 'update'])
                ->middleware('permission:activity.update')
                ->name('update');
            Route::patch('{activity}/reschedule', [ActivityController::class, 'reschedule'])
                ->middleware('permission:activity.update')
                ->name('reschedule');
            Route::delete('{activity}', [ActivityController::class, 'destroy'])
                ->middleware('permission:activity.delete')
                ->name('destroy');
            Route::patch('{activity}/status', [ActivityController::class, 'changeStatus'])
                ->middleware('permission:activity.change_status')
                ->name('status.update');
            Route::post('{activity}/participants', [ActivityParticipantController::class, 'store'])
                ->middleware('permission:activity.manage_participants')
                ->name('participants.store');
            Route::post('{activity}/participants/bulk', [ActivityParticipantController::class, 'bulkStore'])
                ->middleware('permission:activity.manage_participants')
                ->name('participants.bulk');
            Route::delete('{activity}/participants/{participant}', [ActivityParticipantController::class, 'destroy'])
                ->middleware('permission:activity.manage_participants')
                ->name('participants.destroy');
        });
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware(['permission:report.view', 'reports.enabled'])->group(function () {
        Route::get('campaigns', [ReportController::class, 'campaigns'])->name('campaigns.index');
        Route::get('patients', [ReportController::class, 'patients'])->name('patients.index');
        Route::get('attendance', [ReportController::class, 'attendance'])->name('attendance.index');
    });

    // Administration
    Route::prefix('administration')->name('administration.')->group(function () {
        Route::get('users', [\App\Http\Controllers\Administration\UserController::class, 'index'])
            ->middleware('permission:user.view')
            ->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\Administration\UserController::class, 'create'])
            ->middleware('permission:user.create')
            ->name('users.create');
        Route::post('users', [\App\Http\Controllers\Administration\UserController::class, 'store'])
            ->middleware('permission:user.create')
            ->name('users.store');
        Route::get('users/{user}', [\App\Http\Controllers\Administration\UserController::class, 'show'])
            ->middleware('permission:user.view')
            ->name('users.show');
        Route::get('users/{user}/edit', [\App\Http\Controllers\Administration\UserController::class, 'edit'])
            ->middleware('permission:user.update')
            ->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\Administration\UserController::class, 'update'])
            ->middleware('permission:user.update')
            ->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\Administration\UserController::class, 'destroy'])
            ->middleware('permission:user.delete')
            ->name('users.destroy');
        Route::patch('users/{user}/activate', [\App\Http\Controllers\Administration\UserController::class, 'activate'])
            ->middleware('permission:user.update')
            ->name('users.activate');
        Route::patch('users/{user}/deactivate', [\App\Http\Controllers\Administration\UserController::class, 'deactivate'])
            ->middleware('permission:user.update')
            ->name('users.deactivate');
        Route::put('users/{user}/password', [\App\Http\Controllers\Administration\UserController::class, 'updatePassword'])
            ->middleware('permission:user.update')
            ->name('users.password.update');
        Route::get('roles', [\App\Http\Controllers\Administration\RoleController::class, 'index'])
            ->middleware('permission:role.view')
            ->name('roles.index');
        Route::post('roles', [\App\Http\Controllers\Administration\RoleController::class, 'store'])
            ->middleware('permission:role.create')
            ->name('roles.store');
        Route::put('roles/{role}', [\App\Http\Controllers\Administration\RoleController::class, 'update'])
            ->middleware('permission:role.update')
            ->name('roles.update');
        Route::delete('roles/{role}', [\App\Http\Controllers\Administration\RoleController::class, 'destroy'])
            ->middleware('permission:role.delete')
            ->name('roles.destroy');
    });

    require __DIR__.'/settings.php';

    /*
    |--------------------------------------------------------------------------
    | Role middleware example (Super Admin only area — future use)
    |--------------------------------------------------------------------------
    |
    | Route::prefix('system')->middleware('role:super_admin')->group(function () {
    |     Route::get('audit-log', ...);
    | });
    |
    | Custom wrapper middleware example:
    | Route::get('...')->middleware('ensure.permission:campaign.create');
    */
});
