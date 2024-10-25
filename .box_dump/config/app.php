<?php

return [
'name' => 'NTRN',
'version' => app('git.version'),
'env' => (\Phar::running(false) ? 'production' : 'local'),
'providers' => [
App\Providers\AppServiceProvider::class,
],
'key' => env('APP_KEY'),
];
