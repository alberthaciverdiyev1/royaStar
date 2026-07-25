<?php

if (!function_exists('processQuestionImages')) {

    /**
     * Scan translatable question fields for base64 images,
     * save them to storage, and replace content with the URL.
     *
     * Fields: question, variant_a..e, open_answer, explanation
     */
    function processQuestionImages(array &$data): void
    {
        $imageFields = ['question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e', 'open_answer', 'explanation'];

        foreach ($imageFields as $field) {
            if (!isset($data[$field]) || !is_array($data[$field])) {
                continue;
            }

            foreach ($data[$field] as $locale => &$value) {
                if (!is_array($value) || ($value['type'] ?? null) !== 'image') {
                    continue;
                }

                $content = $value['content'] ?? '';

                if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $content, $matches)) {
                    $extension = str_replace('jpeg', 'jpg', $matches[1]);
                    $imageData = base64_decode($matches[2]);

                    $filename = 'questions/' . Illuminate\Support\Str::uuid() . '.' . $extension;

                    Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageData);

                    $value['content'] = Illuminate\Support\Facades\Storage::disk('public')->url($filename);
                }
            }
            unset($value);
        }
    }
}
