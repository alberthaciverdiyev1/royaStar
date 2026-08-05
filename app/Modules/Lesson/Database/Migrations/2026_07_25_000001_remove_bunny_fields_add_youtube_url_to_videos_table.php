<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('videos', function (Blueprint $table) {
                $table->dropUnique('videos_bunny_stream_video_id_unique');
            });
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['url', 'duration', 'thumbnail', 'bunny_stream_video_id', 'status']);
            $table->string('youtube_url')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::table('videos', function (Blueprint $table) {
                $table->string('bunny_stream_video_id')->nullable()->after('youtube_url');
            });
        }

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
            $table->string('url')->comment('Video url');
            $table->string('duration')->comment('Video duration');
            $table->string('thumbnail')->comment('Video thumbnail');
            $table->string('bunny_stream_video_id')->nullable()->unique()->after('lang');
            $table->string('status')->default('active')->after('bunny_stream_video_id');
        });
    }
};
