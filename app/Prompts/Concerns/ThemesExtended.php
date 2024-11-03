<?php

namespace App\Prompts\Concerns;

trait ThemesExtended
{
    public static function getTheme(string $name = ''): array
    {
        return blank($name) ? static::$themes : (static::$themes[$name] ?? []);
    }
}
