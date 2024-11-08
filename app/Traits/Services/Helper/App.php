<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Str;

trait App
{
    protected static array $buildInfo;

    protected static function appCommandsAdd(): array
    {
        $always = [
            \Illuminate\Foundation\Console\KeyGenerateCommand::class,
            \Illuminate\Console\Scheduling\ScheduleListCommand::class,
            \Illuminate\Console\Scheduling\ScheduleRunCommand::class,
            \Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
            \Symfony\Component\Console\Command\DumpCompletionCommand::class,
        ];

        if (static::isPhar()) {
            $env = [
                //
            ];
        } else {
            $env = [
                \LaravelZero\Framework\Commands\StubPublishCommand::class,
                \Illuminate\Foundation\Console\VendorPublishCommand::class,
            ];
        }

        return array_merge($always, $env);
    }

    protected static function appCommandsHidden(): array
    {
        $always = [
            \NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
            \Symfony\Component\Console\Command\HelpCommand::class,
        ];

        if (static::isPhar()) {
            $env = [
                //
            ];
        } else {
            $env = [
                //
            ];
        }

        return array_merge($always, $env);
    }

    protected static function appCommandsRemove(): array
    {
        $always = [
            \LaravelZero\Framework\Commands\MakeCommand::class,
            \LaravelZero\Framework\Commands\BuildCommand::class,
            \LaravelZero\Framework\Commands\RenameCommand::class,
        ];

        if (static::isPhar()) {
            $env = [
                \App\Commands\Customised\AppBuild::class,
                \App\Commands\AppRelease::class,
                \App\Commands\Customised\MakeCommand::class,
                \Illuminate\Database\Console\Factories\FactoryMakeCommand::class,
                \Illuminate\Database\Console\Migrations\MigrateMakeCommand::class,
                \Illuminate\Foundation\Console\ModelMakeCommand::class,
                \Illuminate\Database\Console\Seeds\SeederMakeCommand::class,
                \Illuminate\Foundation\Console\TestMakeCommand::class,
                \NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand::class,
                \Illuminate\Foundation\Console\StubPublishCommand::class,
                \Illuminate\Foundation\Console\VendorPublishCommand::class,
            ];
        } else {
            $env = [
                //                \App\Commands\AppUpdate::class,
            ];
        }

        return array_merge($always, $env);
    }

    public static function appCommands(string $section = ''): array
    {
        $section = Str::lower($section);

        return match ($section) {
            'add' => static::appCommandsAdd(),
            'hidden' => static::appCommandsHidden(),
            'remove' => static::appCommandsRemove(),
            '' => [
                'add' => static::appCommandsAdd(),
                'hidden' => static::appCommandsHidden(),
                'remove' => static::appCommandsRemove(),
            ],
            default => [],
        };
    }

    public static function appProviders(): array
    {
        $always = [
            \Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            \Illuminate\Queue\QueueServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
        ];

        if (static::isPhar()) {
            $env = [];
        } else {
            $env = [];
        }

        return array_merge($always, $env);
    }

    public static function buildInfo(string $key, mixed $default = null): mixed
    {
        if (! static::isPhar()) {
            return value($default);
        }

        if (! isset(static::$buildInfo)) {
            static::$buildInfo = file_exists(base_path('build.json')) ? static::jsonDecode(file_get_contents(base_path('build.json')), []) : [];
        }

        return data_get(static::$buildInfo, $key, $default);
    }
}
