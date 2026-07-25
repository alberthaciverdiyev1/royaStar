<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('type')->default('topic_based')->after('name');
            $table->foreignId('topic_id')->nullable()->constrained()->cascadeOnDelete()->after('type');
            $table->foreignId('lesson_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('lesson_id')->change();

            $table->dropForeign(['topic_id']);
            $table->dropColumn(['type', 'topic_id']);
        });
    }
};
