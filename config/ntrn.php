<?php

declare(strict_types=1);

return [
    'mixins' => [
        'pattern' => '/@mixin\s*([^\s*]+)/',
        'replace' => true,
        'list' => [
            App\Mixins\StringableMixin::class,
            App\Mixins\StrMixin::class,
            App\Mixins\FilesystemMixin::class,
        ],
    ],
];
