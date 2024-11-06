<?php

namespace App\Providers;

use App\Services\GitHub\Contracts\GitHubContract;
use App\Services\GitHub\GitHubManager;
use Illuminate\Support\ServiceProvider;

class GitHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GitHubContract::class, function ($app) {
            return new GitHubManager($app['http.client']);
        });
    }

    public function boot(): void
    {

    }
}
