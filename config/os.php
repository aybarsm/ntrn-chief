<?php

return [
    'iptables' => [
        'pattern' => '/^(ip(6?)tables)\s+-[A-Z]/',
        'check' => [
            'replace' => '$1 -C',
        ],
    ],
];
