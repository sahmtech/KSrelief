<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->decimal('height_cm', 5, 1)->nullable()->after('gender');
            $table->decimal('weight_kg', 5, 1)->nullable()->after('height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->dropColumn(['height_cm', 'weight_kg']);
        });
    }
};
