<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_quick_fill_presets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('implant_company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('preset_json');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['implant_company_id', 'status', 'sort_order'], 'op_quick_fill_company_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_quick_fill_presets');
    }
};
