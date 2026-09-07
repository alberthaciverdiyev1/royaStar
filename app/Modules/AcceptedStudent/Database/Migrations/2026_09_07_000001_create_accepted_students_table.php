<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accepted_students', function (Blueprint $table) {
            $table->id();
            // Optional link to an existing platform user/student (used only as a
            // source of the photo/name — it does NOT grant login access here).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('image')->nullable();
            // Custom manual score set by admin (university entrance exam points), NOT platform XP.
            $table->unsignedInteger('exam_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('exam_points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accepted_students');
    }
};
