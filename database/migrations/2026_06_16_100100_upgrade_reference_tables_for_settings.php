<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso2', 2)->nullable()->after('code');
            $table->string('iso3', 3)->nullable()->after('iso2');
            $table->string('phone_code', 10)->nullable()->after('iso3');
            $table->string('status')->default('active')->after('phone_code');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        DB::table('countries')->update([
            'iso3' => DB::raw('code'),
            'status' => DB::raw("CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('status')->default('active')->after('name_ar');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        DB::table('cities')->update([
            'status' => DB::raw("CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('specialties', function (Blueprint $table) {
            $table->text('description')->nullable()->after('code');
            $table->string('status')->default('active')->after('description');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        DB::table('specialties')->update([
            'status' => DB::raw("CASE WHEN is_active = 1 THEN 'active' ELSE 'inactive' END"),
        ]);

        Schema::table('specialties', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('campaign_status_id')->nullable()->after('expected_patients')->constrained('campaign_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campaign_status_id');
        });

        Schema::table('specialties', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['description', 'status']);
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn('status');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['iso2', 'iso3', 'phone_code', 'status']);
        });
    }
};
