<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Illuminate\Filesystem\join_paths;

if (! \Phar::running(false)) {
    if (! function_exists('dev_temp')) {
        function dev_temp(string $file = ''): string
        {
            $tempDir = join_paths(config('dev.temp'), Str::uuid());
            File::ensureDirectoryExists($tempDir);

            return blank($file) ? $tempDir : join_paths($tempDir, $file);
        }
    }
}

if (! function_exists('joinPaths')) {
    function joinPaths(string $basePath = '', ...$paths): string
    {
        if (blank($basePath)) {
            $basePath = base_path();
        }

        return join_paths($basePath, ...$paths);
    }
}

if (! function_exists('joinBasePath')) {
    function joinBasePath(...$paths): string
    {
        if (blank($paths)) {
            return base_path();
        } elseif ($paths[0] == base_path()) {
            array_shift($paths);
        }

        return join_paths(base_path(), ...$paths);
    }
}

if (! function_exists('config_value')) {
    function config_value(string $key, mixed $default = null): mixed
    {
        return value(config($key, $default));
    }
}
