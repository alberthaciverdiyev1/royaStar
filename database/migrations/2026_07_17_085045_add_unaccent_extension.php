<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $this->ensureExtension('unaccent');
        $this->ensureExtension('pg_trgm');
    }

    public function down(): void
    {
        // Extensions are not dropped on rollback to avoid breaking dependent data.
    }

    private function ensureExtension(string $name): void
    {
        $exists = DB::select("SELECT 1 FROM pg_extension WHERE extname = ?", [$name]);

        if (!empty($exists)) {
            return;
        }

        try {
            DB::unprepared("CREATE EXTENSION IF NOT EXISTS \"{$name}\"");
        } catch (\Throwable $e) {
            Log::warning("Could not create PostgreSQL extension '{$name}': {$e->getMessage()}");
        }
    }
};
