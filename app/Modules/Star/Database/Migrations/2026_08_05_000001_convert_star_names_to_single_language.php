<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stars')) {
            return;
        }

        // Convert localized JSON name/description to plain text (single-language).
        DB::statement(
            "ALTER TABLE stars ALTER COLUMN name TYPE VARCHAR(500) "
            . "USING (CASE WHEN name IS NULL THEN NULL "
            . "WHEN json_typeof(name) = 'object' THEN name->>'az' "
            . "WHEN json_typeof(name) = 'string' THEN name #>> '{}' "
            . "ELSE NULL END)"
        );
        DB::statement(
            "ALTER TABLE stars ALTER COLUMN description TYPE VARCHAR(1000) "
            . "USING (CASE WHEN description IS NULL THEN NULL "
            . "WHEN json_typeof(description) = 'object' THEN description->>'az' "
            . "WHEN json_typeof(description) = 'string' THEN description #>> '{}' "
            . "ELSE NULL END)"
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('stars')) {
            return;
        }

        DB::statement("ALTER TABLE stars ALTER COLUMN name TYPE JSON USING (to_json(name::text))");
        DB::statement("ALTER TABLE stars ALTER COLUMN description TYPE JSON USING (to_json(description::text))");
    }
};
