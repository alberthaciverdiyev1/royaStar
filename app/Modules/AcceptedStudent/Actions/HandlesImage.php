<?php

namespace App\Modules\AcceptedStudent\Actions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Processes the optional `image` field of an AcceptedStudent payload.
 * Base64 data URIs (new uploads from the admin panel) are persisted to the
 * public disk and replaced with their URL. Plain URLs / storage paths are kept
 * unchanged so records linked to existing users can reuse their avatar.
 */
trait HandlesImage
{
    private function persistImage(array $data): array
    {
        // Empty string means "clear the image".
        if (array_key_exists('image', $data) && $data['image'] === '') {
            $data['image'] = null;

            return $data;
        }

        if (empty($data['image']) || !is_string($data['image'])) {
            return $data;
        }

        if (!str_starts_with($data['image'], 'data:image/')) {
            return $data;
        }

        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/s', $data['image'], $matches)) {
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

        $filename = 'accepted-students/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($filename, $fileData);

        $data['image'] = Storage::disk('public')->url($filename);

        return $data;
    }
}
