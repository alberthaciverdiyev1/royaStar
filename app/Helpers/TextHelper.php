<?php

use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('website_text_stored')) {
    /**
     * Return the admin-customized texts (key => value) for the public website.
     * Cached for performance; flushed whenever texts are updated.
     */
    function website_text_stored(): array
    {
        return Cache::remember('website_texts.stored', 3600, function () {
            $setting = Setting::query()->first();

            return is_array($setting?->texts) ? $setting->texts : [];
        });
    }
}

if (!function_exists('website_text_fallbacks')) {
    /**
     * Flatten the config/website_texts.php registry into key => fallback.
     * A tiny static cache makes repeated calls cheap.
     */
    function website_text_fallbacks(): array
    {
        static $flat = null;

        if ($flat === null) {
            $flat = [];
            foreach (config('website_texts', []) as $group) {
                foreach ($group['keys'] ?? [] as $key => $fallback) {
                    $flat[$key] = $fallback;
                }
            }
        }

        return $flat;
    }
}

if (!function_exists('text')) {
    /**
     * Resolve a website text. Returns the admin-customized value when set,
     * otherwise the default fallback from the registry.
     *
     * Placeholders: text('topics.showing', ['count' => 3, 'total' => 10])
     *
     * @param  string  $key
     * @param  array<string, scalar>  $replace
     */
    function text(string $key, array $replace = []): string
    {
        $fallbacks = website_text_fallbacks();
        $stored = website_text_stored();

        $value = array_key_exists($key, $stored) && trim((string) $stored[$key]) !== ''
            ? (string) $stored[$key]
            : ($fallbacks[$key] ?? $key);

        foreach ($replace as $name => $replacement) {
            $value = str_replace('{' . $name . '}', (string) $replacement, $value);
        }

        return $value;
    }
}

if (!function_exists('text_has_override')) {
    /**
     * Whether an admin has customized the given key (used by the admin UI).
     */
    function text_has_override(string $key): bool
    {
        $stored = website_text_stored();

        return array_key_exists($key, $stored) && trim((string) $stored[$key]) !== '';
    }
}
