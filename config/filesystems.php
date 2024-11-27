<?php

declare(strict_types=1);

use App\Services\Helper;

return [
    'default' => 'local',
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path(),
        ],
    ],
    'standard' => [
        'names' => [
            'expected' => env('FS_STANDARD_NAMES_EXPECTED', '/^[\w\-\.]+$/'),
            //            'search' => Helper::jsonDecode(env('FS_STANDARD_NAMES_SEARCH'), ['/\s+/', '/\[(.*?)\]|\((.*?)\)|\{(.*?)\}/', '/(\[|\(\{)(.*?)(\]|\)\})/', '/\s/']),
            //            'replace' => Helper::jsonDecode(env('FS_STANDARD_NAMES_REPLACE'), [' ', '-$1$2$3', '-$1', '_']),
            //            'search' => Helper::jsonDecode(env('FS_STANDARD_NAMES_SEARCH'), ['/\s+/', '/[\[|\(|\{](.*?)[\]|\)|\}]/', '/\s/']),
            //            'replace' => Helper::jsonDecode(env('FS_STANDARD_NAMES_REPLACE'), [' ', '-$1', '_']),
            'search' => Helper::jsonDecode(env('FS_STANDARD_NAMES_SEARCH'), ['/\s+/', '/\[|\(|\{/', '/\]|\)|\}/', '/\s/']),
            'replace' => Helper::jsonDecode(env('FS_STANDARD_NAMES_REPLACE'), [' ', '-', '', '_']),
            'remainder' => env('FS_STANDARD_NAMES_REMAINDER', '_'),
            'ascii' => env('FS_STANDARD_NAMES_ASCII', true),
        ],
    ],
];
