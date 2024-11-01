<?php

use App\Services\Helper;

return [
    'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => (Helper::isPhar() ? [

    ] : [
        Illuminate\Foundation\Console\VendorPublishCommand::class,
        LaravelZero\Framework\Commands\StubPublishCommand::class,
    ]) + [
        Illuminate\Foundation\Console\KeyGenerateCommand::class,
        Illuminate\Console\Scheduling\ScheduleListCommand::class,
        Illuminate\Console\Scheduling\ScheduleRunCommand::class,
        Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
        Symfony\Component\Console\Command\DumpCompletionCommand::class,
    ],
    'hidden' => [
        NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
        Symfony\Component\Console\Command\HelpCommand::class,
    ],
    'remove' => (Helper::isPhar() ? [
        App\Commands\Customised\AppBuild::class,
        App\Commands\Customised\MakeCommand::class,
        Illuminate\Database\Console\Factories\FactoryMakeCommand::class,
        Illuminate\Database\Console\Migrations\MigrateMakeCommand::class,
        Illuminate\Foundation\Console\ModelMakeCommand::class,
        Illuminate\Database\Console\Seeds\SeederMakeCommand::class,
        Illuminate\Foundation\Console\TestMakeCommand::class,
        NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand::class,
        Illuminate\Foundation\Console\StubPublishCommand::class,
        Illuminate\Foundation\Console\VendorPublishCommand::class,
    ] : [
        App\Commands\AppUpdate::class,
    ]) + [
        LaravelZero\Framework\Commands\MakeCommand::class,
        LaravelZero\Framework\Commands\BuildCommand::class,
        LaravelZero\Framework\Commands\RenameCommand::class,
    ],
];
