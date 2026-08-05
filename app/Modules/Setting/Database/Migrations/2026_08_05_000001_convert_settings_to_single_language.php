<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        // Convert previously localized JSON columns to plain text by extracting
        // the primary (az) value. The project is single-language now.
        foreach (['app_name', 'address', 'about_text', 'terms_text', 'privacy_text'] as $column) {
            DB::statement(
                "ALTER TABLE settings ALTER COLUMN {$column} TYPE VARCHAR(1000) "
                . "USING (CASE WHEN {$column} IS NULL THEN NULL "
                . "WHEN json_typeof({$column}) = 'object' THEN {$column}->>'az' "
                . "WHEN json_typeof({$column}) = 'string' THEN {$column} #>> '{}' "
                . "ELSE NULL END)"
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        foreach (['app_name', 'address', 'about_text', 'terms_text', 'privacy_text'] as $column) {
            DB::statement(
                "ALTER TABLE settings ALTER COLUMN {$column} TYPE JSON "
                . "USING (CASE WHEN {$column} IS NULL THEN NULL ELSE to_json({$column}::text) END)"
            );
        }
    }
};
