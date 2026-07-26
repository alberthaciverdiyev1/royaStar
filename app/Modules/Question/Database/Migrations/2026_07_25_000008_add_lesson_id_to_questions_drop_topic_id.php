<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lesson_id');
        });
    }
};
