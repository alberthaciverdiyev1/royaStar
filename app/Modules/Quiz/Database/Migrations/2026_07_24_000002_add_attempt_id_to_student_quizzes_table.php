<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_quizzes', function (Blueprint $table) {
            $table->foreignId('attempt_id')->nullable()->constrained('quiz_attempts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_quizzes', function (Blueprint $table) {
            $table->dropForeign(['attempt_id']);
            $table->dropColumn('attempt_id');
        });
    }
};
