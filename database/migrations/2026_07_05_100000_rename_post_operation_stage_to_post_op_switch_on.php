<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('patient_stages')
            ->where('code', 'post_operation')
            ->update(['name' => 'Post op / switch on']);
    }

    public function down(): void
    {
        DB::table('patient_stages')
            ->where('code', 'post_operation')
            ->update(['name' => 'Switch On']);
    }
};
