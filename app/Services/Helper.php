<?php

namespace App\Services;

use App\Prompts\Contracts\ProgressContract;
use App\Traits\Services\Helper\App;
use App\Traits\Services\Helper\Attributes;
use App\Traits\Services\Helper\Git;
use App\Traits\Services\Helper\Process;
use App\Traits\Services\Helper\Reflector;
use App\Traits\Services\Helper\System;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

use function Illuminate\Filesystem\join_paths;

class Helper
{
    use App, Attributes, Git, Process, Reflector, System;

    public static array $langMap = [
        'langX.rx' => ['receive', 'receiving'],
        'langX.tx' => ['send', 'sending'],
        'rx' => ['download', 'downloading'],
        'tx' => ['upload', 'uploading'],
    ];

    public static function resolveVersion(string $verInfo, string $pattern, mixed $default = null, bool $segments = false): mixed
    {
        $req = ['major', 'minor', 'patch'];
        preg_match($pattern, $verInfo, $ver);
        if (! Arr::has($ver, $req)) {
            return $default;
        }

        return $segments ? Arr::only($ver, $req) : "{$ver['major']}.{$ver['minor']}.{$ver['patch']}";
    }

    public static function appNextVer(int $step, string $ver = '', string $verPattern = ''): string
    {
        $ver = blank($ver) ? config('app.version') : $ver;
        $verPattern = blank($verPattern) ? config('app.version_pattern') : $verPattern;
        throw_if($step < 1 || $step > 3, "Step [{$step}] is invalid. Use 1 for patch, 2 for minor, or 3 for major.");

        $ver = static::resolveVersion($ver, $verPattern, '', true);
        $ver = array_merge(
            $ver,
            match ($step) {
                3 => ['major' => $ver['major'] + 1, 'minor' => 0, 'patch' => 0],
                2 => ['minor' => $ver['minor'] + 1, 'patch' => 0],
                default => ['patch' => $ver['patch'] + 1],
            }
        );

        return Str::matchesReplace($verPattern, $ver);
    }

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

        $format .= match ($precision) {
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
        )->prepend($fileName)->value();

        $path = join_paths(static::tempBase(), $fileFull);

        if ($create || $createDir) {
            File::ensureDirectoryExists(dirname($path));
        }

        if ($create) {
            File::put($path, '');
        }

        return $path;
    }

    public static function composer(string $path = '', mixed $default = null): mixed
    {
        try {
            $json = File::json(base_path('composer.json'));
        } catch (\Exception $e) {
            return $default;
        }

        return blank($json) ? $default : Arr::get($json, $path, $default);
    }

    public static function firstLine(string $str, bool $lower = false): string
    {
        return Str::of($str)
            ->trim()
            ->replaceMatches('/^\s*[\r\n]+|[\r\n]+\s*\z/', '')
            ->replaceMatches('/(\n\s*){2,}/', "\n")
            ->when($lower, fn (Stringable $str) => $str->lower())
            ->split('#\r?\n#', 2, PREG_SPLIT_NO_EMPTY)
            ->first();
    }

    public static function isPhar(): bool
    {
        return ! blank(\Phar::running(false));
    }

    public static function generateExtendedUlid(bool $md5 = false): string
    {
        $extended = (string) Str::ulid().'|'.Carbon::now('UTC')->toIso8601ZuluString('microsecond');

        return $md5 ? md5($extended) : $extended;
    }

    public static function fileStreamProgress(
        ProgressContract $progress,
        string $remote,
        string $labelSuffix,
        string $labelPrefix = '',
        bool $rx = true,
        bool $langX = false,
    ): ProgressContract {
        $lang = static::$langMap[($langX ? 'langX.' : '').($rx ? 'r' : 't').'x'];
        $verb = Str::title($lang[0]);
        $act = Str::title($lang[1]);
        $label = blank($labelPrefix) ? sprintf('%s file', $verb) : $labelPrefix;
        $label = Str::of($label)->trim()->append(' '.Str::trim($labelSuffix))->trim()->value();
        $remote = Str::trim($remote);

        $progress->label($label)
            ->hint("{$verb} starting", 'initial')
            ->hint("{$act}: {$remote}", 'active')
            ->hint("{$verb} completed: {$remote}", 'submit')
            ->number('', ['type' => 'fileSize', 'options' => [2]]);

        return $progress;
    }

    public static function downloadProgress(
        ProgressContract $progress,
        string $remote,
        string $labelSuffix
    ): ProgressContract {
        return static::fileStreamProgress($progress, $remote, $labelSuffix);
    }

    public static function uploadProgress(
        ProgressContract $progress,
        string $remote,
        string $labelSuffix
    ): ProgressContract {
        return static::fileStreamProgress($progress, $remote, $labelSuffix, '', false);
    }

    public static function phpinfo(int $flags = INFO_ALL): string
    {
        ob_start();
        phpinfo($flags);
        $info = ob_get_contents();
        ob_clean();

        return $info;
    }

    public static function jsonDecode(mixed $json, mixed $default = null, bool $assoc = true, int $depth = 512, int $flags = 0): mixed
    {
        if (is_string($json) && Str::isJson($json)) {
            return json_decode($json, $assoc, $depth, $flags);
        }

        return $default;
    }
}
