<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The signup form and every request class treat grade_id / city_id as
        // optional, but the columns were created NOT NULL — registering
        // without picking a grade/city crashed with a DB error. nullOnDelete()
        // only makes sense on a nullable column anyway.
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->change();
            $table->foreignId('city_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable(false)->change();
            $table->foreignId('city_id')->nullable(false)->change();
        });
    }
};
