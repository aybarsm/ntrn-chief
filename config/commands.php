<?php

use App\Services\Helper;

return [
    'default' => NunoMaduro\LaravelConsoleSummary\SummaryCommand::class,
    'paths' => [app_path('Commands')],
    'add' => Helper::appCommands('add'),
    'hidden' => Helper::appCommands('hidden'),
    'remove' => Helper::appCommands('remove'),
];
