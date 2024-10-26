<?php

namespace App\Services;



use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Illuminate\Filesystem\join_paths;

class Archive
{
    protected static function validateInputs(string $archive, string $destination, bool $overwrite = false): void
    {
        throw_if(! File::exists($archive), \RuntimeException::class, "Archive [{$archive}] does not exist");
        throw_if(! File::isReadable($archive), \RuntimeException::class, "Archive [{$archive}] is not readable");
        throw_if(! $overwrite && File::exists($destination), \RuntimeException::class, "Destination [{$destination}] already exists");
        throw_if(File::exists($destination) && ! File::isWritable($destination), \RuntimeException::class, "Destination [{$destination}] is not writable");

        File::ensureDirectoryExists($destination);
    }

    public static function extractTar(string $archive, string $destination, array|string|null $files = null, bool $overwrite = false): \PharData
    {
        static::validateInputs($archive, $destination, $overwrite);

        $phar = new \PharData($archive);
        $pharCompressed = $phar->isCompressed();

        if ($pharCompressed) {
            // In case archive's directory is not writable, we need to extract it to a temporary directory
            $tempDir = join_paths(sys_get_temp_dir(), Str::uuid());
            File::ensureDirectoryExists($tempDir);
            $tempFile = join_paths($tempDir, basename($archive));
            File::copy($archive, $tempFile);
            $phar = new \PharData($tempFile);
            $phar->decompress();
        }

        $phar->extractTo($destination, $files, $overwrite);

        if ($pharCompressed && isset($tempDir) && File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }

        return $phar;
    }

    public static function extractTarGz(string $archive, string $destination, array|string|null $files = null, bool $overwrite = false): \PharData
    {
        return static::extractTar($archive, $destination, $files, $overwrite);
    }

    public static function extractZip(string $archive, string $destination, array|string|null $files = null, bool $overwrite = false): \ZipArchive
    {
        static::validateInputs($archive, $destination, $overwrite);

        $zip = new \ZipArchive();
        $zip->open($archive);
        $zip->extractTo($destination, $files);
        $zip->close();

        return $zip;
    }

    public static function extractTo(string $archive, string $destination, array|string|null $files = null, bool $overwrite = false): \ZipArchive|\PharData
    {
        static::validateInputs($archive, $destination, $overwrite);

        return match(true) {
            Str::endsWith($archive, '.tar') => static::extractTar($archive, $destination, $files, $overwrite),
            Str::endsWith($archive, '.tar.gz') => static::extractTarGz($archive, $destination, $files, $overwrite),
            Str::endsWith($archive, '.zip') => static::extractZip($archive, $destination, $files, $overwrite),
        };
    }

}
