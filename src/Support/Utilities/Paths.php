<?php

namespace Stillat\BladeParser\Support\Utilities;

use Illuminate\Support\Str;

class Paths
{
    public static function normalizePathWithTrailingSlash(?string $path): ?string
    {
        if ($path == null) {
            return null;
        }

        return Str::finish(self::normalizePath($path), '/');
    }

    public static function normalizePath(?string $path): ?string
    {
        if ($path == null) {
            return null;
        }

        return Str::replace('\\', '/', $path);
    }
}
