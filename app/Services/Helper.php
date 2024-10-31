<?php

namespace App\Services;

use App\Prompts\Contracts\ProgressContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use function Illuminate\Filesystem\join_paths;
use function Symfony\Component\String\s;

class Helper
{
    static public array $langMap = [
        'langX.rx' => ['receive', 'receiving'],
        'langX.tx' => ['send', 'sending'],
        'rx' => ['download', 'downloading'],
        'tx' => ['upload', 'uploading'],
    ];

    public static function tempBase(): string
    {
        return join_paths(sys_get_temp_dir(), '__ntrn_temp');

    }

    public static function ts(): Carbon
    {
        return Carbon::now('UTC');
    }

    public static function tsSafe(string $precision = 'second'): string
    {
        $format = 'Ymd\THi';

        $format .= match($precision){
            's', 'second', 'seconds' => 's',
            'm', 'millisecond', 'milliseconds' => 's.v',
            'µ', 'u', 'microsecond', 'microseconds' => 's.u',
            default => '',
        };

        $format .= '\Z';

        return static::ts()->format($format);
    }

    public static function tempDir(bool $create = false): string
    {

        $path = join_paths(static::tempBase(), static::tsSafe('m'));

        if ($create) {
            File::ensureDirectoryExists($path);
        }

        return $path;
    }

    public static function tempFile(bool $create = false, bool $createDir = false, string $name = '', string $ext = 'tmp'): string
    {
        $fileName = blank($name) ? static::tsSafe('m') : Str::of($name)->trim()->trim('.')->value();

        $fileFull = Str::of($ext)->trim()->trim('.')->when(
            fn (Stringable $ext) => $ext->isNotEmpty(),
            fn (Stringable $ext) => $ext->prepend('.'),
        )
        ->prepend($fileName)
        ->value();

        $path = join_paths(static::tempBase(), $fileFull);

        if ($create || $createDir) {
            File::ensureDirectoryExists(dirname($path));
        }

        if ($create) {
            File::put($path, '');
        }

        return $path;
    }

    public static function isPhar(): bool
    {
        return ! blank(\Phar::running(false));
    }
    public static function generateExtendedUlid(bool $md5 = false): string
    {
        $extended = (string)Str::ulid() . '|' . Carbon::now('UTC')->toIso8601ZuluString('microsecond');

        return $md5 ? md5($extended) : $extended;
    }

    public static function fileStreamProgress(
        ProgressContract $progress,
        string $remote,
        string $labelSuffix,
        string $labelPrefix = '',
        bool $rx = true,
        bool $langX = false,
        ): ProgressContract
    {
        $lang = static::$langMap[($langX ? 'langX.' : '') . ($rx ? 'r' : 't') . 'x'];
        $verb = Str::title($lang[0]);
        $act = Str::title($lang[1]);
        $label = blank($labelPrefix) ? sprintf('%s file', $verb) : $labelPrefix;
        $label = Str::of($label)->trim()->append(' ' . Str::trim($labelSuffix))->trim()->value();
        $remote = Str::trim($remote);

        $progress->label($label)
            ->hint( "{$verb} starting", 'initial')
            ->hint("{$act}: {$remote}", 'active')
            ->hint("{$verb} completed: {$remote}", 'submit')
            ->number('', ['type' => 'fileSize', 'options' => [2]]);

        return $progress;
    }

    public static function downloadProgress(
        ProgressContract $progress,
        string $remote,
        string $labelSuffix
    ): ProgressContract
    {
        return static::fileStreamProgress($progress, $remote, $labelSuffix);
    }

}
