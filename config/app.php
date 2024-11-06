<?php

declare(strict_types=1);

use App\Services\Helper;

return [
    'name' => 'NTRN',
    'env' => (Helper::isPhar() ? 'production' : 'local'),
    'version' => (Helper::isPhar() ? Helper::buildInfo('version', 'Unknown') : app('git.version')),
    'build' => (Helper::isPhar() ? Helper::buildInfo('build', 'Unknown') : 'development'),
    // version pattern must always provide major, minor and patch
    'version_pattern' => env('APP_VERSION_PATTERN', '/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/'),
    'timezone' => 'UTC',
    'providers' => Helper::appProviders(),
    'key' => env('APP_KEY'),
    'update' => [
        'strategy' => env('APP_UPDATE_STRATEGY', 'GITHUB_RELEASE'),
        'version' => [
            'target' => env('APP_UPDATE_VERSION_TARGET', 'latest'),
            // version pattern must always provide major, minor and patch
            'pattern' => env('APP_UPDATE_VERSION_PATTERN', '/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/'),
            'http' => [
                'headers' => Helper::jsonDecode(env('APP_UPDATE_VERSION_QUERY_HTTP_HEADERS'), []),
            ],
        ],
        'auto' => (bool) env('APP_UPDATE_AUTO', false),
        'http' => [
            // assoc json array string, will be overwritten to defaults
            'headers' => Helper::jsonDecode(env('APP_UPDATE_HTTP_HEADERS'), []),
            'timeout' => (int) env('APP_UPDATE_HTTP_TIMEOUT', 60),
        ],
        'strategies' => [
            'github' => [
                'release' => [
                    'owner' => env('APP_UPDATE_STRATEGY_GITHUB_RELEASE_OWNER', 'aybarsm'),
                    'repo' => env('APP_UPDATE_STRATEGY_GITHUB_RELEASE_REPO', 'ntrn-chief'),
                    'token' => env('APP_UPDATE_STRATEGY_GITHUB_RELEASE_TOKEN'),
                    'asset' => [
                        'label' => env('APP_UPDATE_STRATEGY_GITHUB_RELEASE_ASSET_LABEL', value(Helper::systemDist(''))),
                    ],
                ],
            ],
            'direct' => [
                'url' => env('APP_UPDATE_STRATEGY_DIRECT_URL', 'https://s3.blrm.net/vault/ntrn/latest/'.Helper::systemDist('')),
                'version_url' => env('APP_UPDATE_STRATEGY_DIRECT_VERSION_URL', 'https://s3.blrm.net/vault/ntrn/latest/'.Helper::systemDist('').'/version'),
            ],
        ],
    ],
];
