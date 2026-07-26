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
                'extMap' => ['jpeg' => 'jpg'],
            ],
            'audio' => [
                'pattern' => '/^data:audio\/(\w+);base64,(.+)$/',
                'dir' => 'questions/audio',
                'extMap' => ['mpeg' => 'mp3', 'x-m4a' => 'm4a', 'x-wav' => 'wav', 'x-flac' => 'flac'],
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
                    $extension = $config['extMap'][$matches[1]] ?? $matches[1];
                    $fileData = base64_decode($matches[2]);

                    $filename = $config['dir'] . '/' . Illuminate\Support\Str::uuid() . '.' . $extension;

                    Illuminate\Support\Facades\Storage::disk('public')->put($filename, $fileData);

                    $value['content'] = Illuminate\Support\Facades\Storage::disk('public')->url($filename);
                }
            }
            unset($value);
        }
    }
}
