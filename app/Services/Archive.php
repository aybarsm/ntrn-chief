<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

use function Illuminate\Filesystem\join_paths;

class Archive
{
    protected static bool $tryUseOS = true;

    protected static function canUseOS(string $command): bool
    {
        if (! static::$tryUseOS) {
            return false;
        }

        if (! in_array(Str::lower(PHP_OS_FAMILY), ['linux', 'darwin'])) {
            return false;
        }

        $command = Str::of($command)->trim()->start('command -v ')->value();
        $process = Process::run($command);

        return $process->successful();
    }

    protected static function validateInputs(string $archive, string $destination): void
    {
        throw_if(! File::exists($archive), \RuntimeException::class, "Archive [{$archive}] does not exist");
        throw_if(! File::isReadable($archive), \RuntimeException::class, "Archive [{$archive}] is not readable");
        throw_if(File::exists($destination) && ! File::isWritable($destination), \RuntimeException::class, "Destination [{$destination}] is not writable");

        File::ensureDirectoryExists($destination);
    }

    public static function extractTar(string $archive, string $destination, array|string|null $files = null): \PharData|bool
    {
        static::validateInputs($archive, $destination);

        if (static::canUseOS('tar')) {
            $files = is_array($files) ? implode(' ', $files) : $files;
            $command = Str::of("tar -zxf {$archive} -C {$destination}")
                ->when(! blank($files), fn ($str) => $str->append(" {$files}"))
                ->value();
            $process = Process::run($command);

            return $process->successful();
        }

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

        $phar->extractTo($destination, $files);

        if ($pharCompressed && isset($tempDir) && File::exists($tempDir)) {
            File::deleteDirectory($tempDir);
        }

        return $phar;
    }

    public static function extractTarGz(string $archive, string $destination, array|string|null $files = null): \PharData|bool
    {
        return static::extractTar($archive, $destination, $files);
    }

    public static function extractZip(string $archive, string $destination, array|string|null $files = null): \ZipArchive|bool
    {
        static::validateInputs($archive, $destination);

        if (static::canUseOS('unzip')) {
            $process = Process::run("unzip {$archive} -d {$destination}");

            return $process->successful();
        }

        $zip = new \ZipArchive;
        $zip->open($archive);
        $zip->extractTo($destination, $files);
        $zip->close();

        return $zip;
    }

    public static function extractTo(string $archive, string $destination, array|string|null $files = null): \ZipArchive|\PharData|bool
    {
        static::validateInputs($archive, $destination);

        return match (true) {
            Str::endsWith($archive, '.tar') => static::extractTar($archive, $destination, $files),
            Str::endsWith($archive, '.tar.gz') => static::extractTarGz($archive, $destination, $files),
            Str::endsWith($archive, '.zip') => static::extractZip($archive, $destination, $files),
        };
    }
}
