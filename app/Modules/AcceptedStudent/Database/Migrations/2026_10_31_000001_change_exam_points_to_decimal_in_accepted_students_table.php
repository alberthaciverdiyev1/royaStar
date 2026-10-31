<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accepted_students', function (Blueprint $table) {
            $table->decimal('exam_points', 8, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('accepted_students', function (Blueprint $table) {
            $table->unsignedInteger('exam_points')->default(0)->change();
        });
    }
};
