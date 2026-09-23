<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Before the project moved to its current domain (config('app.url')),
 * uploaded assets and rich-text content stored absolute URLs pointing at the
 * legacy hosts below. This migration rewrites only the host part of those URLs
 * so existing images/questions keep working on the new domain.
 */
return new class extends Migration
{
    /** Legacy absolute hosts baked into stored values. */
    private const LEGACY_HOSTS = [
        'https://royastar.foxsoft.agency',
        'http://royastar.foxsoft.agency',
        'https://royastar.jobing.az',
        'http://royastar.jobing.az',
    ];

    /** table => column pairs that may contain absolute URLs to the site itself. */
    private const TARGETS = [
        'accepted_students' => 'image',
        'questions' => 'question',
    ];

    public function up(): void
    {
        $target = rtrim((string) config('app.url'), '/');

        if ($target === '') {
            return;
        }

        foreach (self::TARGETS as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            foreach (self::LEGACY_HOSTS as $host) {
                DB::table($table)
                    ->where($column, 'like', '%'.$host.'%')
                    ->update([
                        $column => DB::raw(
                            sprintf('REPLACE(%s, %s, %s)', $column, DB::getPdo()->quote($host), DB::getPdo()->quote($target))
                        ),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // One-way data normalisation — nothing to revert.
    }
};
