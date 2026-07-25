<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_lessons', function (Blueprint $table) {
            $table->integer('progress')->default(0)->after('lesson_id');
            $table->timestamp('completed_at')->nullable()->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('student_lessons', function (Blueprint $table) {
            $table->dropColumn(['progress', 'completed_at']);
        });
    }
};
