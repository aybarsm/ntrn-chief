<?php

namespace App\Services\GitHub;

use App\Services\GitHub\Contracts\GitHubContract;
use Illuminate\Http\Client\PendingRequest;

class GitHubManager extends AbstractGitHub implements GitHubContract
{
    static PendingRequest $client;

    public function __construct(PendingRequest $client)
    {
        $client->accept('application/vnd.github.v3+json');

    }
}
