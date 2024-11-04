<?php

declare(strict_types=1);

use App\Services\Helper;

return [
    'os' => Helper::systemOs(),
    'arch' => Helper::systemArch(),
    'dist' => Helper::systemDist(),
];
