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
];
