<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class TourMediaPath
{
    public static function isExternalUrl(?string $value): bool
    {
        return $value !== null && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public static function publicUrl(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (self::isExternalUrl($value)) {
            return $value;
        }

        return Storage::disk('public')->url(ltrim($value, '/'));
    }
}
