<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_TABLE = 'settings_legacy';

    public function up(): void
    {
        $settingsTableExists = Schema::hasTable('settings');

        $isLegacyTable = $settingsTableExists
            && Schema::hasColumn('settings', 'key')
            && Schema::hasColumn('settings', 'value');


        if ($isLegacyTable) {
            if (Schema::hasTable(self::LEGACY_TABLE)) {
                throw new RuntimeException(
                    'Settings migration dayandırıldı: settings_legacy cədvəli artıq mövcuddur.'
                );
            }

            Schema::rename('settings', self::LEGACY_TABLE);

            $this->createNewSettingsTable();
            $this->migrateFromLegacy();

            return;
        }

        if (!$settingsTableExists) {
            $this->createNewSettingsTable();
            $this->insertSettingsRow([]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::drop('settings');
        }

        if (Schema::hasTable(self::LEGACY_TABLE)) {
            Schema::rename(self::LEGACY_TABLE, 'settings');
        }
    }

    private function createNewSettingsTable(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('app_name');

            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            $table->string('address', 1000)->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('twitter')->nullable();
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();

            $table->text('about_text')->nullable();
            $table->text('terms_text')->nullable();
            $table->text('privacy_text')->nullable();

            $table->boolean('maintenance_mode')->default(false);

            $table->timestamps();
        });
    }

    private function migrateFromLegacy(): void
    {
        $rows = DB::table(self::LEGACY_TABLE)
            ->pluck('value', 'key')
            ->all();

        $this->insertSettingsRow($rows);
    }

    private function insertSettingsRow(array $rows): void
    {
        DB::table('settings')->insert([
            'app_name' => $this->plainValue($rows['app_name'] ?? null) ?? 'RoyaStar',

            'logo' => $this->nullableString($rows['logo'] ?? null),
            'favicon' => $this->nullableString($rows['favicon'] ?? null),

            'address' => $this->plainValue($rows['address'] ?? null),

            'email' => $this->nullableString($rows['email'] ?? null),
            'phone' => $this->nullableString($rows['phone'] ?? null),

            'facebook' => $this->nullableString($rows['facebook'] ?? null),
            'instagram' => $this->nullableString($rows['instagram'] ?? null),
            'youtube' => $this->nullableString($rows['youtube'] ?? null),
            'twitter' => $this->nullableString($rows['twitter'] ?? null),
            'telegram' => $this->nullableString($rows['telegram'] ?? null),
            'whatsapp' => $this->nullableString($rows['whatsapp'] ?? null),

            'about_text' => $this->plainValue($rows['about_text'] ?? null),
            'terms_text' => $this->plainValue($rows['terms_text'] ?? null),
            'privacy_text' => $this->plainValue($rows['privacy_text'] ?? null),

            'maintenance_mode' => $this->parseBoolean(
                $rows['maintenance_mode'] ?? null
            ),

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Normalize a legacy settings value to a plain single-language string.
     * Handles a plain string or a legacy multi-locale JSON object (takes 'az').
     */
    private function plainValue(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return $decoded['az'] ?? $decoded[array_key_first($decoded)] ?? null;
        }

        return trim((string) $value);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function parseBoolean(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? false;
    }
};
