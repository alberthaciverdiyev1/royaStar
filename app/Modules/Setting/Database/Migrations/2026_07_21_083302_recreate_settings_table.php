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

            $table->json('app_name');

            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            $table->json('address')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('twitter')->nullable();
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();

            $table->json('about_text')->nullable();
            $table->json('terms_text')->nullable();
            $table->json('privacy_text')->nullable();

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
        $appName = $this->parseJsonField($rows['app_name'] ?? null);

        if (empty($appName)) {
            $appName = 'RoyaStar';
        }

        $data = [
            'app_name' => $appName,

            'logo' => $this->nullableString($rows['logo'] ?? null),
            'favicon' => $this->nullableString($rows['favicon'] ?? null),

            'address' => $this->parseJsonField($rows['address'] ?? null),

            'email' => $this->nullableString($rows['email'] ?? null),
            'phone' => $this->nullableString($rows['phone'] ?? null),

            'facebook' => $this->nullableString($rows['facebook'] ?? null),
            'instagram' => $this->nullableString($rows['instagram'] ?? null),
            'youtube' => $this->nullableString($rows['youtube'] ?? null),
            'twitter' => $this->nullableString($rows['twitter'] ?? null),
            'telegram' => $this->nullableString($rows['telegram'] ?? null),
            'whatsapp' => $this->nullableString($rows['whatsapp'] ?? null),

            'about_text' => $this->parseJsonField($rows['about_text'] ?? null),
            'terms_text' => $this->parseJsonField($rows['terms_text'] ?? null),
            'privacy_text' => $this->parseJsonField($rows['privacy_text'] ?? null),

            'maintenance_mode' => $this->parseBoolean(
                $rows['maintenance_mode'] ?? null
            ),

            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach (
            [
                'app_name',
                'address',
                'about_text',
                'terms_text',
                'privacy_text',
            ] as $jsonField
        ) {
            if ($data[$jsonField] !== null) {
                $data[$jsonField] = json_encode(
                    $data[$jsonField],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE
                );
            }
        }

        DB::table('settings')->insert($data);
    }

    private function parseJsonField(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            // Legacy multi-locale object from an older database; the later
            // single-language migration extracts the primary value.
            return $decoded;
        }

        return $value;
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
