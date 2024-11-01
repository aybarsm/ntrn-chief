<?php

use App\Services\Helper;
use Illuminate\Support\Str;

return [
    'name' => 'NTRN',
    'version' => (Helper::isPhar() ? trim(file_get_contents(config_path('app_version'))) : app('git.version')),
    'os' => Helper::os(),
    'arch' => Helper::arch(),
    'dist' => Helper::dist(),
    'env' => (Helper::isPhar() ? 'production' : 'local'),
    'timezone' => 'UTC',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        App\Providers\NtrnServiceProvider::class,
    ],
    'update' => [
        'strategy' => env('APP_UPDATE_STRATEGY', 'DIRECT'),
        'url' => env('APP_UPDATE_URL', 'https://s3.blrm.net/vault/ntrn/latest/' . Helper::dist()),
        'version_query' => [
            'url' => env('APP_UPDATE_VERSION_QUERY_URL', 'https://s3.blrm.net/vault/ntrn/latest/' . Helper::dist() . '/version'),
            'headers' => Helper::jsonDecode(env('APP_UPDATE_VERSION_QUERY_HEADERS'), []),
            'pattern' => env('APP_UPDATE_VERSION_PATTERN', '/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/'),
        ],
        'auto' => (bool) env('APP_UPDATE_AUTO', false),
        'timeout' => (int) env('APP_UPDATE_TIMEOUT', 60),
    ],
    'key' => env('APP_KEY'),
];
