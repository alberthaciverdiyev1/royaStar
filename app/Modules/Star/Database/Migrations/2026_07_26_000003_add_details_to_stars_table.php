<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stars', function (Blueprint $table) {
            $table->string('name', 500)->nullable()->after('type');
            $table->string('description', 1000)->nullable()->after('name');
            $table->string('icon', 50)->nullable()->after('description');
            $table->string('category', 50)->nullable()->after('icon');
            $table->string('group', 50)->nullable()->after('category');
            $table->boolean('is_active')->default(true)->after('group');
            $table->integer('max_per_day')->nullable()->after('is_active');
            $table->integer('sort_order')->default(0)->after('max_per_day');
            $table->integer('point_min')->default(1)->after('point');
            $table->integer('point_max')->nullable()->after('point_min');
            $table->integer('point_default')->default(0)->after('point_max');
        });
    }

    public function down(): void
    {
        Schema::table('stars', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'description', 'icon', 'category', 'group',
                'is_active', 'max_per_day', 'sort_order',
                'point_min', 'point_max', 'point_default',
            ]);
        });
    }
};
