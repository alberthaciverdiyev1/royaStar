<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('pgsql_activity')->hasTable('student_activities')) {
            return;
        }

        Schema::connection('pgsql_activity')->create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('type')->index();
            $table->string('reference_type'); // lesson, quiz, certificate
            $table->unsignedBigInteger('reference_id');
            $table->json('metadata');
            $table->timestamps(6);
            $table->unique(['student_id', 'reference_type', 'reference_id'], 'student_activity_unique');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql_activity')->dropIfExists('student_activities');
    }
};
