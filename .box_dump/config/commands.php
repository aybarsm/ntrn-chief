<?php

return [
'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
'paths' => [app_path('Commands')],
'add' => [
Illuminate\Foundation\Console\KeyGenerateCommand::class,

],
'hidden' => [








],
'remove' => [
LaravelZero\Framework\Commands\MakeCommand::class,
LaravelZero\Framework\Commands\BuildCommand::class,
],
];
