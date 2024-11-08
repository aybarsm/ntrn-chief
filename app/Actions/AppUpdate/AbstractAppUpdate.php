<?php

namespace App\Actions\AppUpdate;

use App\Services\Helper;
use App\Services\TaskingMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Illuminate\Filesystem\join_paths;

abstract class AbstractAppUpdate extends TaskingMethod
{
    protected array $params;
    protected string $appVer;
    protected string $appVerPattern;

    protected string $updateVer;
    protected string $updateTo;
    protected string $updateVerPattern;
    protected bool $latest;
    protected string $stdAppVer;
    protected string $stdUpdateVer;
    protected bool $updateRequired = false;
    protected string $downloadPath;
    protected PendingRequest $client;
    abstract protected function downloadUpdateAsset(): void;

    protected function standardiseVersions(): void
    {
        preg_match($this->appVerPattern, $this->appVer, $appVer);
        throw_if(! Arr::has($appVer, ['major', 'minor', 'patch']), "App version [{$this->appVer}] could not be resolved with pattern [{$this->appVerPattern}]");

        preg_match($this->updateVerPattern, $this->updateVer, $updateVer);
        throw_if(! Arr::has($updateVer, ['major', 'minor', 'patch']), "Update version [{$this->updateVer}] could not be resolved with pattern [{$this->updateVerPattern}]");

        $this->stdAppVer = "{$appVer['major']}.{$appVer['minor']}.{$appVer['patch']}";
        $this->stdUpdateVer = "{$updateVer['major']}.{$updateVer['minor']}.{$updateVer['patch']}";
    }

    protected function checkUpdateRequirement(): void
    {
        $this->taskStopExecution = match($this->latest) {
            true => version_compare($this->stdAppVer, $this->stdUpdateVer, '>='),
            default => version_compare($this->stdAppVer, $this->stdUpdateVer, '=='),
        };
    }

    protected function setParameters(): void
    {
        foreach($this->params as $key => $value) {
            $this->{$key} = $value;
        }

        $this->latest = $this->updateTo == 'latest';
    }

    protected function generateDownloadPath(): string
    {
        $downloadDir = Helper::isPhar() ? dirname(\Phar::running(false)) : base_path();
        $tsSafe = Helper::tsSafe();
        $namePrefix = "{$this->stdUpdateVer}-{$tsSafe}";
        $nameSuffix = Helper::isPhar() ? basename(\Phar::running(false)) : Str::slug(config('app.name'), '_');
        return join_paths($downloadDir, "{$namePrefix}_{$nameSuffix}");
    }

    protected function handleException(
        int $taskPos,
        \ReflectionAttribute $task,
        \Exception $e): void
    {
        $log['app'] = [
            'class' => get_class($this),
            'config' => config('app'),
        ];

        $log['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        Log::error('Update failed', $log);

        $this->taskStopExecution = true;
    }
    public function __destruct()
    {
        if (isset($this->downloadPath)) {
            Context::add('appUpdateFile', $this->downloadPath);
        }
    }

}
