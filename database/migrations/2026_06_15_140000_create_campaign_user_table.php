<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Future architecture: campaign-level access control.
 *
 * When the Campaign module is implemented, enforce access via:
 * 1. Global permission check (Spatie) — e.g. campaign.view
 * 2. Campaign assignment check — user must exist in campaign_user for the target campaign
 *
 * @see \App\Models\CampaignUser
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->comment('FK → campaigns.id (future Campaign module)');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'user_id', 'role_id']);
            $table->index(['user_id', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_user');
    }
};
