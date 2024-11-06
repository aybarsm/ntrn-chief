<?php

namespace App\Actions\AppUpdate;

use App\Services\TaskingMethod;
use Github\Client as GitHubClient;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Arr;
use App\Attributes\TaskMethod;
#[TaskMethod(method: 'getRelease', title: 'Get GitHub Release', bail: true)]
#[TaskMethod(method: 'standardiseVersions', title: 'Standardise Versions', bail: true)]
#[TaskMethod(method: 'checkUpdateRequirement', title: 'Check Update Requirement')]
class GitHubRelease extends TaskingMethod
{
    protected string $appVer;
    protected string $appVerPattern;
    protected string $verQueryPattern;
    protected string $updateTo;
    protected int $timeout;
    protected string $userName;
    protected string $repoName;
    protected bool $latest;
    protected array $release;
    protected string $stdAppVer;
    protected string $stdUpdateTo;
    protected bool $updateRequired = false;

    public function __invoke(
        #[Config('app.version')] string $appVer,
        #[Config('app.version_pattern')] string $appVerPattern,
        #[Config('app.update.version_query.pattern')] string $verQueryPattern,
        #[Config('app.update.version')] string $updateTo,
        #[Config('app.update.timeout')] int $timeout,
        #[Config('github.connections.update.username')] string $userName,
        #[Config('github.connections.update.repo')] string $repoName,
    )
    {
        foreach(get_defined_vars() as $key => $value) {
            $this->{$key} = $value;
        }
        $this->latest = $this->updateTo == 'latest';
        $this->userName = 'crazywhalecc';
        $this->repoName = 'static-php-cli';

        $this->executeTasks();
        dump($this->updateRequired);
    }

    protected function getGitHub(): GitHubClient
    {
        return app('github')->connection('none');
    }

    protected function getRelease(): void
    {
        $github = $this->getGitHub()->repo()->releases();

        $this->release = $this->latest ? $github->latest($this->userName, $this->repoName) : $github->tag($this->userName, $this->repoName, $this->updateTo);
    }

    protected function standardiseVersions(): void
    {
        preg_match($this->appVerPattern, $this->appVer, $appVer);
        throw_if(! Arr::has($appVer, ['major', 'minor', 'patch']), "App version [{$this->appVer}] could not be resolved with pattern [{$this->appVerPattern}]");

        preg_match($this->verQueryPattern, $this->release['tag_name'], $updateTo);
        throw_if(! Arr::has($updateTo, ['major', 'minor', 'patch']), "Update version [{$this->release['tag_name']}] could not be resolved with pattern [{$this->verQueryPattern}]");

        $this->stdAppVer = "{$appVer['major']}.{$appVer['minor']}.{$appVer['patch']}";
        $this->stdUpdateTo = "{$updateTo['major']}.{$updateTo['minor']}.{$updateTo['patch']}";
    }

    protected function checkUpdateRequirement(): void
    {
        if ($this->latest && version_compare($this->stdAppVer, $this->stdUpdateTo, '>=')) {
//            "No latest update available. Current version: {$this->stdAppVer}, Next version: {$this->stdUpdateTo}"
            $this->taskStopExecution = true;
        }elseif (version_compare($this->stdAppVer, $this->stdUpdateTo, '==')){
//            "Update not required. Current version: {$this->stdAppVer}, Next version: {$this->stdUpdateTo}"
            $this->taskStopExecution = true;
        }else {
            $this->updateRequired = true;
        }
    }


}
