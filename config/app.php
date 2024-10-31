<?php

use App\Services\Helper;

return [
    'name' => 'NTRN',
    'version' => (Helper::isPhar() ? trim(file_get_contents(config_path('app_version'))) : app('git.version')),
    'env' => (Helper::isPhar() ? 'production' : 'local'),
    'timezone' => 'UTC',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        App\Providers\NtrnServiceProvider::class,
    ],
    'update' => [
        'auto' => (bool) env('APP_UPDATE_AUTO', false),
        'timeout' => (int) env('APP_UPDATE_TIMEOUT', 60),
    ],
    'key' => env('APP_KEY'),
];
