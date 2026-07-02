<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_operation_defaults', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('implant_company_id')->constrained()->cascadeOnDelete();
            $table->json('defaults_json');
            $table->timestamps();

            $table->unique(['campaign_id', 'implant_company_id'], 'campaign_op_defaults_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_operation_defaults');
    }
};
