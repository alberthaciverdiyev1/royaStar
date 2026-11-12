<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single-row table: general settings shared by every music track.
        Schema::create('music_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('play_on_all')->default(true);
            // Page keys where music is allowed. Ignored when play_on_all is true.
            $table->json('pages')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_settings');
    }
};
