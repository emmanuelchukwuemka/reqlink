<?php

namespace App\Support;

class UrlHelper
{
    /**
     * Root-relative storage paths (avatar, evidence_file, cover_image, ...) are
     * fine for Blade, which resolves them against the current browser origin.
     * A React Native client has no implicit origin, so every such field must
     * be made absolute before it goes into a JSON response.
     */
    public static function absolute(?string $path): ?string
    {
        if (!$path) {
            return $path;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
    }
}
