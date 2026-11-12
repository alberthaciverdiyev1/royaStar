<?php

namespace App\Modules\Music\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Persists the optional `cover` field of a music track payload.
 * Base64 data URIs (new uploads from the admin panel) are written to the
 * public disk and replaced with their URL. Plain URLs / storage paths are kept
 * unchanged.
 */
trait HandlesCover
{
    private function persistCover(array $data): array
    {
        // Empty string means "clear the cover".
        if (array_key_exists('cover', $data) && $data['cover'] === '') {
            $data['cover'] = null;

            return $data;
        }

        if (empty($data['cover']) || !is_string($data['cover'])) {
            return $data;
        }

        if (!str_starts_with($data['cover'], 'data:image/')) {
            return $data;
        }

        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/s', $data['cover'], $matches)) {
            return $data;
        }

        // Allowlist safe raster formats (SVG intentionally excluded — XSS risk).
        $extMap = ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png', 'gif' => 'gif', 'webp' => 'webp'];
        $mime = strtolower($matches[1]);
        $extension = $extMap[$mime] ?? null;

        if ($extension === null) {
            return $data;
        }

        $fileData = base64_decode($matches[2], true);
        if ($fileData === false || $fileData === '') {
            return $data;
        }

        $filename = 'music/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($filename, $fileData);

        $data['cover'] = Storage::disk('public')->url($filename);

        return $data;
    }
}
