<?php

if (!function_exists('processQuestionMedia')) {

    /**
     * Scan question fields for base64 images and audio,
     * save them to storage, and replace content with the URL.
     *
     * Fields: question, variant_a..e, open_answer, explanation
     */
    function processQuestionMedia(array &$data): void
    {
        $mediaFields = ['question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e', 'open_answer', 'explanation'];

        $mimeMap = [
            'image' => [
                'pattern' => '/^data:image\/(\w+);base64,(.+)$/',
                'dir' => 'questions',
                // Allowlist of safe raster formats. SVG is intentionally excluded (XSS risk).
                'extMap' => ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png', 'gif' => 'gif', 'webp' => 'webp'],
            ],
            'audio' => [
                'pattern' => '/^data:audio\/(\w+);base64,(.+)$/',
                'dir' => 'questions/audio',
                'extMap' => ['mpeg' => 'mp3', 'mp3' => 'mp3', 'x-m4a' => 'm4a', 'x-wav' => 'wav', 'wav' => 'wav', 'x-flac' => 'flac', 'flac' => 'flac'],
            ],
        ];

        foreach ($mediaFields as $field) {
            if (!isset($data[$field]) || !is_array($data[$field])) {
                continue;
            }

            foreach ($data[$field] as &$value) {
                if (!is_array($value) || !in_array($value['type'] ?? null, ['image', 'audio'], true)) {
                    continue;
                }

                $type = $value['type'];
                $config = $mimeMap[$type];
                $content = $value['content'] ?? '';

                if (preg_match($config['pattern'], $content, $matches)) {
                    // Only allow known MIME types — never trust raw user input as extension.
                    $mime = strtolower($matches[1]);
                    $extension = $config['extMap'][$mime] ?? null;
                    if ($extension === null) {
                        continue;
                    }

                    $fileData = base64_decode($matches[2], true);
                    if ($fileData === false || $fileData === '') {
                        continue;
                    }

                    $filename = $config['dir'] . '/' . Illuminate\Support\Str::uuid() . '.' . $extension;

                    Illuminate\Support\Facades\Storage::disk('public')->put($filename, $fileData);

                    $value['content'] = Illuminate\Support\Facades\Storage::disk('public')->url($filename);
                }
            }
            unset($value);
        }
    }
}

if (!function_exists('contentForLocale')) {

    /**
     * Read question content for the given locale.
     *
     * Question content can be stored either as a locale-keyed structure
     * (["az" => [...], "en" => [...]]) — produced by seeders — or as a plain
     * content-block array ([["type" => "text", "content" => ...]]) — produced
     * by the admin panel. This helper returns the right block array for both.
     */
    function contentForLocale(array|string|null $content, string $locale): array
    {
        if (!is_array($content) || empty($content)) {
            return [];
        }

        // Locale-keyed structure → pick the requested locale, falling back to az.
        if (isset($content[$locale]) || isset($content['az']) || isset($content['en']) || isset($content['ru'])) {
            return $content[$locale] ?? $content['az'] ?? $content['en'] ?? $content['ru'] ?? [];
        }

        // Plain content-block array → use it directly.
        return $content;
    }
}

if (!function_exists('localizeContentBlocks')) {

    /**
     * Replicate a plain content-block array into all supported locales so the
     * rest of the app (web + API) can read it consistently. Locale-keyed input
     * is returned unchanged.
     */
    function localizeContentBlocks(array|string|null $content, array $locales = ['az', 'en', 'ru']): array|string|null
    {
        if (!is_array($content) || empty($content)) {
            return $content;
        }

        // Already locale-keyed.
        if (isset($content['az']) || isset($content['en']) || isset($content['ru'])) {
            return $content;
        }

        $localized = [];
        foreach ($locales as $locale) {
            $localized[$locale] = $content;
        }

        return $localized;
    }
}

if (!function_exists('normalizeQuestionLocales')) {

    /**
     * Normalize every translatable content field on a question payload so
     * admin-created questions (plain block arrays) are stored in the same
     * locale-keyed shape the web + API expect.
     */
    function normalizeQuestionLocales(array &$data): void
    {
        $localeFields = ['question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e', 'open_answer', 'explanation'];

        foreach ($localeFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = localizeContentBlocks($data[$field] ?? null);
            }
        }
    }
}

if (!function_exists('renderVideoEmbed')) {

    /**
     * Render an explanation video URL as an embeddable player.
     * YouTube links become iframes; anything else falls back to <video controls>.
     */
    function renderVideoEmbed(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $url = trim($url);

        // YouTube — normalize watch / youtu.be / shorts links into an embeddable iframe.
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            $videoId = $m[1];
            return '<div class="mt-3 rounded-xl overflow-hidden border border-[rgb(var(--surface-container-high))/0.6] bg-black/5">'
                . '<iframe class="w-full aspect-video" src="https://www.youtube.com/embed/' . e($videoId) . '?rel=0" '
                . 'title="Explanation video" frameborder="0" '
                . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
                . 'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>'
                . '</div>';
        }

        // Generic video file (mp4, webm, etc.).
        return '<div class="mt-3 rounded-xl overflow-hidden border border-[rgb(var(--surface-container-high))/0.6] bg-black/5">'
            . '<video class="w-full aspect-video" src="' . e($url) . '" controls preload="none"></video>'
            . '</div>';
    }
}

if (!function_exists('renderContentBlocks')) {

    /**
     * Render content block array into HTML.
     * text → escaped text, image → <img>, audio → <audio controls>.
     */
    function renderContentBlocks(array|string|null $blocks): string
    {
        if ($blocks === null || $blocks === '') {
            return '';
        }
        if (is_string($blocks)) {
            return e($blocks);
        }
        if (empty($blocks)) {
            return '';
        }

        $html = '';
        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'text';
            $content = $block['content'] ?? '';

            switch ($type) {
                case 'image':
                    $html .= '<img src="' . e($content) . '" alt="" class="max-w-full h-auto rounded-lg my-1.5 shadow-sm" style="max-height:320px">';
                    break;
                case 'audio':
                    $html .= '<audio controls class="w-full my-1.5 h-10 rounded-lg"><source src="' . e($content) . '"></audio>';
                    break;
                default:
                    $html .= e($content);
                    break;
            }
        }

        return $html;
    }
}
