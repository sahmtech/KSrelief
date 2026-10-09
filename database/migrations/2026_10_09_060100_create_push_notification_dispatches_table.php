<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('push_notification_dispatches')) {
            return;
        }

        Schema::create('push_notification_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 80);
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable();
            $table->unsignedInteger('target_users')->default(0);
            $table->unsignedInteger('tokens_attempted')->default(0);
            $table->unsignedInteger('tokens_succeeded')->default(0);
            $table->unsignedInteger('tokens_failed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_dispatches');
    }
};
