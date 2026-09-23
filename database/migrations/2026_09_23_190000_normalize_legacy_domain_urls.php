<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Before the project moved to its current domain (config('app.url')),
 * uploaded assets and rich-text/JSON content stored absolute URLs pointing at
 * the legacy hosts below.
 *
 * We only rewrite the bare hostname (e.g. "royastar.foxsoft.agency" →
 * "royastar.name.az") instead of a full "https://host" string. This also
 * matches hosts embedded inside JSON (escaped as "https:\/\/host\/…") and
 * covers both http/https and protocol-relative URLs.
 */
return new class extends Migration
{
    /** Legacy hostnames baked into stored values. */
    private const LEGACY_HOSTS = [
        'royastar.foxsoft.agency',
        'royastar.jobing.az',
    ];

    /** table => column pairs that may contain absolute URLs to the site itself. */
    private const TARGETS = [
        'accepted_students' => 'image',
        'questions' => 'question',
    ];

    public function up(): void
    {
        $newHost = $this->currentHost();

        if ($newHost === '') {
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
                        $column => DB::raw(sprintf(
                            'REPLACE(%s, %s, %s)',
                            $column,
                            DB::getPdo()->quote($host),
                            DB::getPdo()->quote($newHost),
                        )),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // One-way data normalisation — nothing to revert.
    }

    private function currentHost(): string
    {
        $url = rtrim((string) config('app.url'), '/');

        return (string) (parse_url($url, PHP_URL_HOST) ?: $url);
    }
};
