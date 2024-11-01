<?php

namespace App\Commands;

use App\Services\Helper;
use App\Traits\Configable;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use App\Framework\Commands\Command;
use Illuminate\Container\Attributes\Config;
use App\Attributes\NtrnHelper;
use function Illuminate\Filesystem\join_paths;

class AppUpdate extends Command
{
    use Configable;
    protected $signature = 'app:update
    {--assume-ver= : Assume the current version}
    {--no-cleanup : Do not cleanup temporary files}';
    protected $description = 'Update the application';
    protected ?string $tempFile = null;
    protected ?string $verNext = null;

    protected PendingRequest $request;
    protected ?TransferStats $stats = null;
    protected ?Response $response = null;
    protected bool $initalised = false;

    public function __construct(
        #[Config('app.version', '')] protected string $ver,
        #[Config('app.update.strategy', '')] protected string $strategy,
        #[Config('app.update.url', '')] protected string $url,
        #[Config('app.update.auto', false)] protected bool $auto,
        #[Config('app.update.version_query.url', '')] protected string $verQUrl,
        #[Config('app.update.version_query.headers', [])] protected array $verQHeaders,
        #[Config('app.update.version_query.pattern', '')] protected string $verQPattern,
    )
    {
        $this->strategy = Str::upper($this->strategy);

        parent::__construct();
    }

    protected function logException(\Exception $e): void
    {
        $log['app'] = [
            'version' => $this->ver,
            'nextVersion' => $this->verNext,
            'autoUpdate' => $this->auto,
            'strategy' => $this->strategy,
            'url' => $this->url,
        ];

        $log['exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

//        $log['process'] = [];
//        if ($this->lastProcess){
//            $log['process'] = [
//                'exitCode' => $this->lastProcess->exitCode(),
//                'output' => $this->lastProcess->output(),
//                'errorOutput' => $this->lastProcess->errorOutput(),
//            ];
//        }

        $log['stats'] = [];
        if ($this->stats){
            $log['stats'] = [
                'effectiveUri' => $this->stats->getEffectiveUri()->__toString(),
                'handler' => $this->stats->getHandlerStats(),
                'errorHandlerData' => $this->stats->getHandlerErrorData(),
                'transferTime' => $this->stats->getTransferTime(),
            ];
        }

        $log['response'] = [];
        if ($this->response){
            $log['response'] = [
                'status' => $this->response->status(),
                'headers' => $this->response->headers(),
                'clientError' => $this->response->clientError()
            ];
        }

        Log::error("Update failed", $log);
    }

    protected function downloadFile(): bool
    {
        $this->request = Http::sink($this->tempFile)
            ->timeout(config('app.update.timeout', 60))
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->withOptions([
                // Avoid decoupling the instances
                'on_stats' => function (TransferStats $transferStats) use(&$stats) {
                    $stats = $transferStats;
                }
            ]);

        try {
            $this->response = $this->request->get($this->url);
        } catch (\Exception $e){
            $this->stats = $stats;
            $this->logException($e);
            return false;
        }

        $this->stats = $stats;
        Log::notice("Update file from {$this->url} downloaded to {$this->tempFile}");

        return true;
    }

    protected function processCommand(string $command, string $path = '', int $timeout = 0): ProcessResult|bool
    {
        try {
            return Process::command($command)
                ->when(! blank($path), fn ($process) => $process->path($path))
                ->when($timeout > 0, fn ($process) => $process->timeout($timeout))
                ->run()
                ->throw();
        } catch (\Exception $e){
            $this->logException($e);
            return false;
        }
    }

    public function handle(): void
    {
        if ($this->option('assume-ver') !== null){
            $this->ver = $this->option('assume-ver');
        }

//        if (! Helper::isPhar()){
//            return;
//        }

        $invalid = false;
        if ($invalid = ! in_array($this->strategy, ['DIRECT', 'GITHUB_RELEASE', 'GITHUB_TAG'])) {
            Log::warning("Invalid update strategy: {$this->strategy}");
        }

        if ($invalid = ! Str::isUrl($this->url)){
            Log::warning("Invalid update URL: {$this->url}");
        }

        if ($invalid){
            return;
        }

//        $this->tempFile = join_paths(Helper::tempDir(true), 'ntrn_update');
        $this->tempFile = join_paths(dirname(\Phar::running(false)), 'ntrn_update');
        if (File::exists($this->tempFile)){
            File::delete($this->tempFile);
            Log::notice("Existing update file removed: {$this->tempFile}");
        }
        $this->initalised = true;

        if ($this->strategy == 'DIRECT'){
            if (! $this->downloadFile()){
                return;
            }
            File::chmod($this->tempFile, octdec('0750'));

            $process = $this->processCommand("{$this->tempFile} --version");

            if ($process === false){
                return;
            }

            $version = Helper::firstLine($process->output());
            preg_match('/v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $version, $verSegments);

            if (! Arr::has($verSegments, ['major', 'minor', 'patch'])) {
                Log::warning("Version could not be parsed: {$version}");
                return;
            }

            $this->verNext = "v{$verSegments['major']}.{$verSegments['minor']}.{$verSegments['patch']}";
        }else {
            Log::warning("Update strategy not implemented: {$this->strategy}");
            return;
        }

        if (version_compare($this->verNext, $this->ver, '<=')) {
            Log::notice("No update available. Current version: {$this->ver}, Next version: {$this->verNext}");
            return;
        }

        if (! $this->downloadFile()){
            return;
        }

        $runningPhar = \Phar::running(false);

        try {
            File::move($this->tempFile, $runningPhar);
        } catch (\Exception $e){
            $this->logException($e);
            return;
        }

        Log::notice("App update from {$this->ver} to {$this->verNext} successful.");
    }

    public function schedule(Schedule $schedule): void
    {
         $schedule->command(static::class)->hourly();
    }

    public function __destruct()
    {
        if (! $this->initalised){
            return;
        }

        if (! $this->option('no-cleanup') && $this->tempFile && File::exists(dirname($this->tempFile))){
            File::deleteDirectory(dirname($this->tempFile));
        }
    }
}
