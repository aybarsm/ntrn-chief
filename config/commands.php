<?php

return [
    'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => [
        Illuminate\Foundation\Console\KeyGenerateCommand::class,
//        Illuminate\Foundation\Console\VendorPublishCommand::class,
    ],
    'hidden' => [
//        NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
//        Symfony\Component\Console\Command\DumpCompletionCommand::class,
//        Symfony\Component\Console\Command\HelpCommand::class,
//        Illuminate\Console\Scheduling\ScheduleRunCommand::class,
//        Illuminate\Console\Scheduling\ScheduleListCommand::class,
//        Illuminate\Console\Scheduling\ScheduleFinishCommand::class,
//        Illuminate\Foundation\Console\VendorPublishCommand::class,
//        LaravelZero\Framework\Commands\StubPublishCommand::class,
    ],
    'remove' => [
        LaravelZero\Framework\Commands\MakeCommand::class,
        LaravelZero\Framework\Commands\BuildCommand::class,
    ],
];
