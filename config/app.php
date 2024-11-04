<?php

declare(strict_types=1);

use App\Services\Helper;

return [
    'name' => 'NTRN',
    'env' => (Helper::isPhar() ? 'production' : 'local'),
    'version' => (Helper::isPhar() ? Helper::buildVersion('Unknown') : app('git.version')),
    'build' => [
        'time' => (Helper::isPhar() ? Helper::buildTime('Unknown') : 'development'),
        'os' => Helper::systemOs(),
        'arch' => Helper::systemArch(),
        'dist' => (Helper::isPhar() ? Helper::buildArch(Helper::arch()) : Helper::arch()),
    ],
    'version_pattern' => env('APP_VERSION_PATTERN', '/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/'),
    'timezone' => 'UTC',
    'providers' => Helper::appProviders(),
    'update' => [
        'auth' => [
            'type' => env('APP_UPDATE_AUTH_TYPE', 'Token'),
            'token' => env('APP_UPDATE_AUTH_TOKEN'),
            'token_type' => env('APP_UPDATE_AUTH_TOKEN_TYPE', 'Bearer'),
        ],
        'strategy' => env('APP_UPDATE_STRATEGY', 'GITHUB_RELEASE'),
        'url' => env('APP_UPDATE_URL', 'https://github.com/aybarsm/ntrn-chief'),
        'version' => [
            'url' => env('APP_UPDATE_VERSION_URL', 'https://s3.blrm.net/vault/ntrn/latest/'.Helper::dist().'/version'),
            'headers' => Helper::jsonDecode(env('APP_UPDATE_VERSION_HEADERS'), []),
            'pattern' => env('APP_UPDATE_VERSION_PATTERN', '/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/'),
        ],
        'auto' => (bool) env('APP_UPDATE_AUTO', false),
        'timeout' => (int) env('APP_UPDATE_TIMEOUT', 60),
    ],
    'key' => env('APP_KEY'),
];
