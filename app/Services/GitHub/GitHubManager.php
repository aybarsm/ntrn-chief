<?php

namespace App\Services\GitHub;

use App\Services\GitHub\Contracts\GitHubContract;
use App\Services\Helper;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\PendingRequest;

class GitHubManager extends AbstractGitHub implements GitHubContract
{
    public static ?PendingRequest $devClient;

    public static PendingRequest $updateClient;

    public function __construct(
        #[Config('app.update.strategies.github.release.owner')] string $updateOwner,
        #[Config('app.update.strategies.github.release.repo')] string $updateRepo,
        #[Config('app.update.strategies.github.release.token')] ?string $updateToken,
        #[Config('app.update.http.timeout')] int $updateHttpTimeout,
        #[Config('app.update.http.headers')] array $updateHttpHeaders,
        #[Config('dev.github.owner')] ?string $devOwner,
        #[Config('dev.github.repo')] ?string $devRepo,
        #[Config('dev.github.token')] ?string $devToken,
        #[Config('dev.github.http.timeout')] ?int $devHttpTimeout,
        #[Config('dev.github.http.headers')] ?array $devHttpHeaders,
    ) {
        $client = new PendingRequest;
        $client->accept('application/vnd.github.v3+json');

        if (! Helper::isPhar() && ! blank($devOwner) && ! blank($devRepo) && ! blank($devToken)) {
            static::$devClient = $client;
            static::$devClient->baseUrl("https://api.github.com/repos/{$devOwner}/{$devRepo}");
            static::$devClient->withToken($devToken);
            static::$devClient->timeout($devHttpTimeout);
            if (! blank($devHttpHeaders)) {
                static::$devClient->withHeaders($devHttpHeaders);
            }
        } else {
            static::$devClient = null;
        }

        static::$updateClient = $client;
        static::$updateClient->baseUrl("https://api.github.com/repos/{$updateOwner}/{$updateRepo}");
        if (! blank($updateToken)) {
            static::$updateClient->withToken($updateToken);
        }
        static::$updateClient->timeout($updateHttpTimeout);
        if (! blank($updateHttpHeaders)) {
            static::$updateClient->withHeaders($updateHttpHeaders);
        }
    }

    public function getDevClient(): ?PendingRequest
    {
        return static::$devClient;
    }

    public function getUpdateClient(): ?PendingRequest
    {
        return static::$updateClient;
    }
}