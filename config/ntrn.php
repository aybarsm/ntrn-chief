<?php

declare(strict_types=1);

return [
    'conf' => [
        'cache' => [
            'key' => env('NTRN_CONF_CACHE_KEY', 'app'),
            'store' => env('NTRN_CONF_CACHE_STORE', ''),
        ],
    ],
    'configs' => env('NTRN_CONFIGS', ''),
    'views' => env('NTRN_VIEWS', ''),
    'migrations' => env('NTRN_MIGRATIONS', ''),
    'mixins' => [
        'pattern' => '/@mixin\s*([^\s*]+)/',
        'replace' => true,
        'list' => [
            App\Mixins\ApplicationMixin::class,
            App\Mixins\ArrMixin::class,
            App\Mixins\StringableMixin::class,
            App\Mixins\StrMixin::class,
            App\Mixins\FilesystemMixin::class,
            App\Mixins\DBMixin::class,
        ],
    ],
];
