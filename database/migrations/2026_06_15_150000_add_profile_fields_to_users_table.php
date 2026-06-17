<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 20)->nullable()->unique()->after('email');
            $table->enum('gender', ['male', 'female'])->nullable()->after('mobile');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('gender');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mobile', 'gender', 'status', 'last_login_at']);
        });
    }
};
