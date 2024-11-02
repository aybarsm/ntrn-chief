<?php

return [
    'mixins' => [
        'pattern' => '/@mixin\s*([^\s*]+)/',
        'replace' => true,
        'list' => [
            App\Mixins\StringableMixin::class,
            App\Mixins\StrMixin::class,
        ],
    ],
];
