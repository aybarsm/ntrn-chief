<?php

namespace App\Actions\AppUpdate;

use App\Services\TaskingMethod;
use Github\Client as GitHubClient;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Arr;
use App\Attributes\TaskMethod;
#[TaskMethod('getRelease', 'Get GitHubRelease')]
class GitHubRelease extends TaskingMethod
{
    protected object $object;
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
        $this->executeTasks();
        dump($this->tasks);

//        $object = (object)get_defined_vars();
//        $object->userName = 'crazywhalecc';
//        $object->repoName = 'static-php-cli';
//        $object->latest = $object->updateTo == 'latest';
//        $object->resolved = (object)[];
//        $object->log = (object)[];
//
//        $result = Pipeline::send($object)->through([
//            function ($object, $next) {
//                $object = $this->getRelease($object);
//                return $this->stageResult($object, $next);
//            },
//            function ($object, $next) {
//                $object = $this->standardiseVersions($object);
//                return $this->stageResult($object, $next);
//            },
//            function ($object, $next) {
//                $object = $this->standardiseVersions($object);
//                return $this->stageResult($object, $next);
//            },
//        ])->thenReturn();
//
//        $object = $this->getRelease($object);
//        $object = $this->standardiseVersions($object);
//        dump($object);
    }

    protected function logEntry(object $object, string $message, string $level = 'info'): object
    {
        if (! isset($object->log->{$level})) {
            $object->log->{$level} = [];
        }

        $object->log->{$level}[] = $message;

        return $object;
    }

    protected function stageResult(object $object, \Closure $next, bool $isLast = false): mixed
    {
        if (isset($object->log->error) && count($object->log->error) > 0) {
            return false;
        }

        return $next($object);
    }

    protected function standardiseVersions(object $object): object
    {
        preg_match($object->appVerPattern, $object->appVer, $appVer);
        preg_match($object->verQueryPattern, $object->resolved->release['tag_name'], $updateTo);

        if (! Arr::has($appVer, ['major', 'minor', 'patch'])) {
            $object = $this->logEntry($object, "App version [{$object->appVer}] could not be resolved with pattern [{$object->appVerPattern}]", 'error');
        }elseif (Arr::has($updateTo, ['major', 'minor', 'patch'])) {
            $object = $object = $this->logEntry($object, "Update version [{$object->resolved->release['tag_name']}] could not be resolved with pattern [{$object->verQueryPattern}]", 'error');
        }

        $object->resolved->updateTo = "{$updateTo['major']}.{$updateTo['minor']}.{$updateTo['patch']}";
        $object->resolved->appVer = "{$appVer['major']}.{$appVer['minor']}.{$appVer['patch']}";

        return $object;
    }

    protected function getGitHub(): GitHubClient
    {
        return app('github')->connection('none');
    }

    protected function getRelease(object $object): object
    {
        $github = $this->getGitHub()->repo()->releases();

        if ($object->latest) {
            $object->resolved->release = $github->latest($object->userName, $object->repoName);
        }else {
            $object->resolved->release = $github->tag($object->userName, $object->repoName, $object->updateTo);
        }

        return $object;
    }
}
