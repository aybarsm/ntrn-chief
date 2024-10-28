<?php

return [
    'name' => 'NTRN',
    'version' => (\Phar::running(false) ? trim(file_get_contents(config_path('app_version'))) : app('git.version')),
    'env' => (\Phar::running(false) ? 'production' : 'local'),
    'providers' => [
        App\Providers\AppServiceProvider::class,
//        Rahul900Day\LaravelConsoleSpinner\LaravelConsoleSpinnerServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        App\Providers\NtrnServiceProvider::class,
//        Illuminate\Concurrency\ConcurrencyServiceProvider::class,
    ],
    'key' => env('APP_KEY'),
];
