<?php

namespace App\Providers;

use App\Services\GitHub\Contracts\GitHubContract;
use App\Services\GitHub\GitHubManager;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;

class GitHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GitHubContract::class, GitHubManager::class);
    }

    public function boot(): void
    {

    }
}
