<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('bunny_stream_video_id')->nullable()->unique()->after('lang');
            $table->string('status')->default('active')->after('bunny_stream_video_id');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['bunny_stream_video_id', 'status']);
        });
    }
};
