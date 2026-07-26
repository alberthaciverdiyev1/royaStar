<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lessons ALTER COLUMN name TYPE VARCHAR(255) USING (name->>'az')");
            DB::statement("ALTER TABLE lessons ALTER COLUMN name SET NOT NULL");
            DB::statement("ALTER TABLE lessons ALTER COLUMN description TYPE TEXT USING (description->>'az')");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE lessons ALTER COLUMN description TYPE JSON USING to_json(description)");
            DB::statement("ALTER TABLE lessons ALTER COLUMN name TYPE JSON USING to_json(name)");
        }
    }
};
