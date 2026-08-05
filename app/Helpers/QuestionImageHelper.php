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
