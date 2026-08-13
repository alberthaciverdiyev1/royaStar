<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'texts')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('texts')->nullable()->after('privacy_text');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'texts')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('texts');
            });
        }
    }
};
