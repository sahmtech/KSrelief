<?php

use App\Http\Controllers\Api\V1\ActivityController as ApiActivityController;
use App\Http\Controllers\Api\V1\ActivityParticipantController as ApiActivityParticipantController;
use App\Http\Controllers\Api\V1\AttendanceController as ApiAttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BootstrapController;
use App\Http\Controllers\Api\V1\MedicalRecordController;
use App\Http\Controllers\Api\V1\OperationsBootstrapController;
use App\Http\Controllers\Api\V1\PatientActivityController;
use App\Http\Controllers\Api\V1\PatientAttachmentController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\PatientImportController;
use App\Http\Controllers\Api\V1\PatientSearchController;
use App\Http\Controllers\Api\V1\PatientWorkflowController;
use App\Http\Controllers\Api\V1\TransportationLocationController as ApiTransportationLocationController;
use App\Http\Controllers\Api\V1\TransportationPassengerController as ApiTransportationPassengerController;
use App\Http\Controllers\Api\V1\TransportationTripController as ApiTransportationTripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

        Route::get('bootstrap/patients-module', [BootstrapController::class, 'patientsModule'])
            ->name('bootstrap.patients-module');

        Route::get('bootstrap/operations-module', [OperationsBootstrapController::class, 'module'])
            ->name('bootstrap.operations-module');

        Route::prefix('operations')->name('operations.')->group(function (): void {
            Route::prefix('attendance')->name('attendance.')->group(function (): void {
                Route::get('quick', [ApiAttendanceController::class, 'quickSheet'])->name('quick');
                Route::post('bulk', [ApiAttendanceController::class, 'bulkStore'])->name('bulk');
                Route::get('/', [ApiAttendanceController::class, 'index'])->name('index');
                Route::post('/', [ApiAttendanceController::class, 'store'])->name('store');
                Route::get('{attendance}', [ApiAttendanceController::class, 'show'])->name('show');
                Route::put('{attendance}', [ApiAttendanceController::class, 'update'])->name('update');
                Route::delete('{attendance}', [ApiAttendanceController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('transportation')->name('transportation.')->group(function (): void {
                Route::get('locations/search', [ApiTransportationLocationController::class, 'search'])
                    ->name('locations.search');
                Route::post('locations', [ApiTransportationLocationController::class, 'store'])
                    ->name('locations.store');

                Route::get('trips', [ApiTransportationTripController::class, 'index'])->name('trips.index');
                Route::post('trips', [ApiTransportationTripController::class, 'store'])->name('trips.store');
                Route::get('trips/{trip}', [ApiTransportationTripController::class, 'show'])->name('trips.show');
                Route::put('trips/{trip}', [ApiTransportationTripController::class, 'update'])->name('trips.update');
                Route::delete('trips/{trip}', [ApiTransportationTripController::class, 'destroy'])->name('trips.destroy');
                Route::patch('trips/{trip}/status', [ApiTransportationTripController::class, 'changeStatus'])
                    ->name('trips.status');
                Route::get('trips/{trip}/passenger-options', [ApiTransportationTripController::class, 'passengerOptions'])
                    ->name('trips.passenger-options');
                Route::post('trips/{trip}/passengers', [ApiTransportationPassengerController::class, 'store'])
                    ->name('trips.passengers.store');
                Route::delete('trips/{trip}/passengers/{passenger}', [ApiTransportationPassengerController::class, 'destroy'])
                    ->name('trips.passengers.destroy');
            });

            Route::prefix('activities')->name('activities.')->group(function (): void {
                Route::get('calendar/events', [ApiActivityController::class, 'calendarEvents'])
                    ->name('calendar.events');
                Route::get('/', [ApiActivityController::class, 'index'])->name('index');
                Route::post('/', [ApiActivityController::class, 'store'])->name('store');
                Route::get('{activity}', [ApiActivityController::class, 'show'])->name('show');
                Route::put('{activity}', [ApiActivityController::class, 'update'])->name('update');
                Route::patch('{activity}/reschedule', [ApiActivityController::class, 'reschedule'])->name('reschedule');
                Route::patch('{activity}/status', [ApiActivityController::class, 'changeStatus'])->name('status');
                Route::delete('{activity}', [ApiActivityController::class, 'destroy'])->name('destroy');
                Route::get('{activity}/participant-options', [ApiActivityController::class, 'participantOptions'])
                    ->name('participant-options');
                Route::post('{activity}/participants', [ApiActivityParticipantController::class, 'store'])
                    ->name('participants.store');
                Route::post('{activity}/participants/bulk', [ApiActivityParticipantController::class, 'bulkStore'])
                    ->name('participants.bulk');
                Route::delete('{activity}/participants/{participant}', [ApiActivityParticipantController::class, 'destroy'])
                    ->name('participants.destroy');
            });
        });

        Route::get('patients/search', PatientSearchController::class)->name('patients.search');

        Route::prefix('patients/import')->name('patients.import.')->group(function (): void {
            Route::get('meta', [PatientImportController::class, 'meta'])->name('meta');
            Route::get('template', [PatientImportController::class, 'template'])->name('template');
            Route::get('batches', [PatientImportController::class, 'index'])->name('batches.index');
            Route::post('batches', [PatientImportController::class, 'store'])->name('batches.store');
            Route::get('batches/{batch}', [PatientImportController::class, 'show'])->name('batches.show');
            Route::post('batches/{batch}/approve', [PatientImportController::class, 'approve'])->name('batches.approve');
            Route::get('batches/{batch}/errors', [PatientImportController::class, 'downloadErrors'])->name('batches.errors');
        });

        Route::apiResource('patients', PatientController::class)->except(['create', 'edit']);

        Route::get('patients/{patient}/brief', [PatientController::class, 'brief'])
            ->name('patients.brief');

        Route::prefix('patients/{patient}/workflow')->name('patients.workflow.')->group(function (): void {
            Route::get('timeline', [PatientWorkflowController::class, 'timeline'])->name('timeline');
            Route::get('history', [PatientWorkflowController::class, 'history'])->name('history');
            Route::post('stage', [PatientWorkflowController::class, 'changeStage'])->name('change-stage');
        });

        Route::prefix('patients/{patient}/records')->name('patients.records.')->group(function (): void {
            Route::get('/', [MedicalRecordController::class, 'index'])->name('index');
            Route::get('schema', [MedicalRecordController::class, 'schema'])->name('schema');
            Route::get('electrode-types', [MedicalRecordController::class, 'electrodeTypes'])->name('electrode-types');
            Route::get('follow-up-defaults', [MedicalRecordController::class, 'followUpDefaults'])->name('follow-up-defaults');
            Route::get('operation-defaults', [MedicalRecordController::class, 'operationDefaults'])->name('operation-defaults');
            Route::get('pre-operation-defaults', [MedicalRecordController::class, 'preOperationDefaults'])->name('pre-operation-defaults');
            Route::get('post-operation-defaults', [MedicalRecordController::class, 'postOperationDefaults'])->name('post-operation-defaults');
            Route::get('campaign-operation-defaults', [MedicalRecordController::class, 'campaignOperationDefaults'])->name('campaign-operation-defaults');
            Route::get('operation-quick-fill', [MedicalRecordController::class, 'operationQuickFill'])->name('operation-quick-fill');
            Route::post('/', [MedicalRecordController::class, 'store'])->name('store');
            Route::get('{record}', [MedicalRecordController::class, 'show'])->name('show');
            Route::put('{record}', [MedicalRecordController::class, 'update'])->name('update');
            Route::delete('{record}', [MedicalRecordController::class, 'destroy'])->name('destroy');
            Route::get('{record}/export-operation-pdf', [MedicalRecordController::class, 'exportOperationPdf'])
                ->name('export-operation-pdf');
        });

        Route::post('patients/{patient}/attachments', [PatientAttachmentController::class, 'store'])
            ->name('patients.attachments.store');
        Route::get('patients/{patient}/attachments/{attachment}/preview', [PatientAttachmentController::class, 'preview'])
            ->name('patients.attachments.preview');
        Route::get('patients/{patient}/attachments/{attachment}/download', [PatientAttachmentController::class, 'download'])
            ->name('patients.attachments.download');
        Route::delete('patients/{patient}/attachments/{attachment}', [PatientAttachmentController::class, 'destroy'])
            ->name('patients.attachments.destroy');

        Route::get('patients/{patient}/activities', [PatientActivityController::class, 'index'])
            ->name('patients.activities.index');
    });
});
