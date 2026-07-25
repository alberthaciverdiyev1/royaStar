<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_lessons', function (Blueprint $table) {
            $table->integer('last_position')->nullable()->after('progress')->comment('Last watched position in seconds');
            $table->timestamp('last_watched_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_lessons', function (Blueprint $table) {
            $table->dropColumn(['last_position', 'last_watched_at']);
        });
    }
};
