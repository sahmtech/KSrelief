<?php

use App\Http\Controllers\Settings\ActivityTypeController;
use App\Http\Controllers\Settings\AttendanceStatusController;
use App\Http\Controllers\Settings\CampaignStatusController;
use App\Http\Controllers\Settings\CityController;
use App\Http\Controllers\Settings\CountryController;
use App\Http\Controllers\Settings\MemberRoleController;
use App\Http\Controllers\Settings\PatientEligibilityStatusController;
use App\Http\Controllers\Settings\PatientStageController;
use App\Http\Controllers\Settings\RecordCodeBackfillController;
use App\Http\Controllers\Settings\SettingsDashboardController;
use App\Http\Controllers\Settings\SpecialtyController;
use App\Http\Controllers\Settings\TransportationLocationController;
use App\Models\CampaignStatusRecord;
use Illuminate\Support\Facades\Route;

Route::bind('campaign_status', fn (string $value) => CampaignStatusRecord::query()->findOrFail($value));

Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/', [SettingsDashboardController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('dashboard');

    Route::post('backfill-record-codes', [RecordCodeBackfillController::class, 'store'])
        ->middleware(['permission:settings.view', 'app.debug'])
        ->name('backfill-record-codes');

    $settingsResource = function (string $uri, string $controller, string $prefix): void {
        Route::resource($uri, $controller)->middleware([
            'index' => "permission:{$prefix}.view",
            'show' => "permission:{$prefix}.view",
            'create' => "permission:{$prefix}.create",
            'store' => "permission:{$prefix}.create",
            'edit' => "permission:{$prefix}.update",
            'update' => "permission:{$prefix}.update",
            'destroy' => "permission:{$prefix}.delete",
        ]);
    };

    $settingsResource('countries', CountryController::class, 'country');
    $settingsResource('cities', CityController::class, 'city');
    $settingsResource('specialties', SpecialtyController::class, 'specialty');
    $settingsResource('member-roles', MemberRoleController::class, 'member_role');
    $settingsResource('patient-eligibility-statuses', PatientEligibilityStatusController::class, 'patient_status');
    $settingsResource('patient-stages', PatientStageController::class, 'stage_settings');
    $settingsResource('activity-types', ActivityTypeController::class, 'activity_type');
    $settingsResource('transportation-locations', TransportationLocationController::class, 'transport_location');
    $settingsResource('attendance-statuses', AttendanceStatusController::class, 'attendance_status');
    $settingsResource('campaign-statuses', CampaignStatusController::class, 'campaign_status');
});
