<?php

namespace App\Providers;

use App\Models\ActivityType;
use App\Models\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Campaign;
use App\Models\CampaignStatusRecord;
use App\Models\City;
use App\Models\Country;
use App\Models\MemberRole;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientStage;
use App\Models\Specialty;
use App\Models\TransportationLocation;
use App\Models\MedicalRecord;
use App\Models\Member;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Models\TransportationTrip;
use App\Models\Activity;
use App\Policies\ActivityPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\TransportationTripPolicy;
use App\Policies\DashboardPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\MemberPolicy;
use App\Policies\PatientPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\Settings\ActivityTypePolicy;
use App\Policies\Settings\AttendanceStatusPolicy;
use App\Policies\Settings\CampaignStatusRecordPolicy;
use App\Policies\Settings\CityPolicy;
use App\Policies\Settings\CountryPolicy;
use App\Policies\Settings\MemberRolePolicy;
use App\Policies\Settings\PatientEligibilityStatusPolicy;
use App\Policies\Settings\PatientStagePolicy;
use App\Policies\Settings\SpecialtyPolicy;
use App\Policies\Settings\TransportationLocationPolicy;
use App\Support\AdminMenu;
use App\Support\DashboardAccessResolver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(Campaign::class, CampaignPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(TransportationTrip::class, TransportationTripPolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(MedicalRecord::class, MedicalRecordPolicy::class);
        Gate::policy(Country::class, CountryPolicy::class);
        Gate::policy(City::class, CityPolicy::class);
        Gate::policy(Specialty::class, SpecialtyPolicy::class);
        Gate::policy(MemberRole::class, MemberRolePolicy::class);
        Gate::policy(PatientEligibilityStatus::class, PatientEligibilityStatusPolicy::class);
        Gate::policy(PatientStage::class, PatientStagePolicy::class);
        Gate::policy(ActivityType::class, ActivityTypePolicy::class);
        Gate::policy(TransportationLocation::class, TransportationLocationPolicy::class);
        Gate::policy(AttendanceStatus::class, AttendanceStatusPolicy::class);
        Gate::policy(CampaignStatusRecord::class, CampaignStatusRecordPolicy::class);

        Gate::define('viewDashboard', [DashboardPolicy::class, 'viewDashboard']);
        Gate::define('viewCampaignDashboard', [DashboardPolicy::class, 'viewCampaignDashboard']);
        app(DashboardAccessResolver::class)->registerGate();

        Route::bind('record', function (string $value, \Illuminate\Routing\Route $route): MedicalRecord {
            $patient = $route->parameter('patient');

            if ($patient instanceof Patient) {
                return $patient->medicalRecords()->findOrFail($value);
            }

            return MedicalRecord::query()->findOrFail($value);
        });

        view()->composer('*', function ($view): void {
            $locale = app()->getLocale();

            if (auth()->check()) {
                auth()->user()->loadMissing('roles');
            }

            $view->with([
                'adminMenu' => AdminMenu::build(),
                'adminName' => config('admin.name'),
                'adminTagline' => __('layout.brand_tagline'),
                'htmlDir' => $locale === 'ar' ? 'rtl' : 'ltr',
                'availableLocales' => config('admin.locales'),
                'currentLocale' => $locale,
            ]);
        });
    }
}
