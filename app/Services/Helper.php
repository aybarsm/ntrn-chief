<?php

namespace App\Services;

use App\Prompts\Contracts\ProgressContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class Helper
{
    static public array $langMap = [
        'langX.rx' => ['receive', 'receiving'],
        'langX.tx' => ['send', 'sending'],
        'rx' => ['download', 'downloading'],
        'tx' => ['upload', 'uploading'],
    ];
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
