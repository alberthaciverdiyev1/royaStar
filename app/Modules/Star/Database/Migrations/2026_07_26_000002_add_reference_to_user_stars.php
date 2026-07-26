<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_stars', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('star_id');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->json('metadata')->nullable()->after('reference_id');

            $table->unique(['user_id', 'star_id', 'reference_type', 'reference_id'], 'user_star_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_stars', function (Blueprint $table) {
            $table->dropUnique('user_star_ref_unique');
            $table->dropColumn(['reference_type', 'reference_id', 'metadata']);
        });
    }
};
