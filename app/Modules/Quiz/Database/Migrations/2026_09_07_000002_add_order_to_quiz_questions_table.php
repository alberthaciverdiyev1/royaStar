<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unsignedSmallInteger('order')->default(0)->after('question_id');
        });

        // Backfill existing rows preserving their original insertion order per quiz.
        DB::statement(<<<'SQL'
            UPDATE quiz_questions q
            SET "order" = ranked.rn
            FROM (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY quiz_id ORDER BY id) AS rn
                FROM quiz_questions
            ) ranked
            WHERE q.id = ranked.id
        SQL);
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
