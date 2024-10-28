<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Illuminate\Filesystem\join_paths;

if (! \Phar::running(false)){
    if (! function_exists('dev_temp')) {
        function dev_temp(string $file = ''): string
        {
            $tempDir = join_paths(config('dev.temp'), Str::uuid());
            File::ensureDirectoryExists($tempDir);

            return blank($file) ? $tempDir : join_paths($tempDir, $file);
        }
    }
}
