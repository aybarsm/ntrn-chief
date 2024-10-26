<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        Http::macro('progress', function ($callback) {
            return $this->withOptions([
                'progress' => fn (...$parameters) => $callback(...$parameters),
            ]);
        });
    }
}
