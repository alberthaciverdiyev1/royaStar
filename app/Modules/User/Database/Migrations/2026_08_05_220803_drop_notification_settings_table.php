<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Notification system has been removed — drop the leftover settings table.
     */
    public function up(): void
    {
        Schema::dropIfExists('notification_settings');
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_settings')) {
            Schema::create('notification_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_email')->default(true)->comment('Email notification');
                $table->boolean('is_task')->default(true)->comment('Task notification');
                $table->timestamps();
            });
        }
    }
};
