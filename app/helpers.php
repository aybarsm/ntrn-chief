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
