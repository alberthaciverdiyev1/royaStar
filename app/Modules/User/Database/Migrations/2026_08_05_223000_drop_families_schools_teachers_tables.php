<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parent, School, and Teacher modules have been removed — drop their leftover tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('school_registration_requests');
        Schema::dropIfExists('families');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('teachers');
    }

    public function down(): void
    {
        if (!Schema::hasTable('teachers')) {
            Schema::create('teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('name')->nullable();
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('families')) {
            Schema::create('families', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('school_registration_requests')) {
            Schema::create('school_registration_requests', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('name')->nullable();
                $table->string('surname')->nullable();
                $table->string('phone')->nullable();
                $table->string('password')->nullable();
                $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
                $table->string('school_name')->nullable();
                $table->string('school_no')->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }
};
