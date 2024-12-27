<?php

declare(strict_types=1);

use App\Services\Helper;

return [
    'configs' => env('NTRN_CONFIGS', ''),
    'views' => env('NTRN_VIEWS', ''),
    'mixins' => [
        'pattern' => '/@mixin\s*([^\s*]+)/',
        'replace' => true,
        'list' => [
            App\Mixins\ArrMixin::class,
            App\Mixins\StringableMixin::class,
            App\Mixins\StrMixin::class,
            App\Mixins\FilesystemMixin::class,
        ],
    ],
];
