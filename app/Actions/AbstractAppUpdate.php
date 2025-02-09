<?php

namespace App\Actions;

use App\Attributes\TaskMethod;
use App\Services\Helper;
use App\Services\TaskingMethod;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

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

    protected bool $updateRequired;

    protected string $downloadPath;

    protected PendingRequest $client;

    protected bool $force = false;

    protected ?OutputInterface $output = null;

    abstract protected function downloadUpdateAsset(): void;

    protected function standardiseVersions(): void
    {
        preg_match($this->appVerPattern, $this->appVer, $appVer);
        throw_if(! Arr::has($appVer, ['major', 'minor', 'patch']), "App version [{$this->appVer}] could not be resolved with pattern [{$this->appVerPattern}]");

        preg_match($this->updateVerPattern, $this->updateVer, $updateVer);
        throw_if(! Arr::has($updateVer, ['major', 'minor', 'patch']), "Update version [{$this->updateVer}] could not be resolved with pattern [{$this->updateVerPattern}]");

        $this->stdAppVer = "{$appVer['major']}.{$appVer['minor']}.{$appVer['patch']}";
        $this->stdUpdateVer = "{$updateVer['major']}.{$updateVer['minor']}.{$updateVer['patch']}";

        Log::info("Standardised Versions: App: [{$this->stdAppVer}] - Update: [{$this->stdUpdateVer}]");
    }

    protected function checkUpdateRequirement(): void
    {
        $this->updateRequired = match ($this->latest) {
            true => version_compare($this->stdAppVer, $this->stdUpdateVer, '<'),
            default => version_compare($this->stdAppVer, $this->stdUpdateVer, '!='),
        };

        $this->taskStopExecution = ! $this->force && ! $this->updateRequired;
    }

    protected function setParameters(): void
    {
        foreach ($this->params as $key => $value) {
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
        TaskMethod $task,
        \Exception $exception): void
    {
        //        $exception = [
        //            'class' => get_class($e),
        //            'message' => $e->getMessage(),
        //            'code' => $e->getCode(),
        //            'file' => $e->getFile(),
        //            'line' => $e->getLine(),
        //        ];

        $context = [
            'task' => [
                'pos' => $taskPos,
                'method' => $task,
            ],
            'exception' => $exception,
            'app' => [
                'config' => config('app'),
            ],
        ];

        Log::error('Update failed', $context);

        $this->taskStopExecution = true;
    }

    protected function handleAfter(): void
    {
        if (isset($this->updateRequired)) {
            Context::add('appUpdateRequired', $this->updateRequired);
        }

        if (isset($this->downloadPath)) {
            Context::add('appUpdateFile', $this->downloadPath);
        }
    }
}
