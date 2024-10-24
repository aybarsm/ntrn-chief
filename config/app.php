<?php

return [
    'name' => 'NTRN',
    'version' => app('git.version'),
    'env' => (\Phar::running(false) ? 'production' : 'local'),
//    'env' => 'production',
    'providers' => [
        App\Providers\AppServiceProvider::class,
    ],
    'key' => env('APP_KEY'),
];
