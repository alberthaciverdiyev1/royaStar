<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop topic_id FK and column
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        // Convert name from JSON to plain text (take az locale value)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE quizzes ALTER COLUMN name TYPE text USING (name->>'az')");
        }
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->json('name')->change();
            $table->foreignId('topic_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
