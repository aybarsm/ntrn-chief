<?php

namespace App\Commands;

use App\Services\GitHub;
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
use function Illuminate\Filesystem\join_paths;

class AppUpdate extends Command
{
    use Configable;
    protected $signature = 'app:update
    {--assume-ver= : Assume the current version}
    {--no-cleanup : Do not cleanup temporary files}';
    protected $description = 'Update the application';
    protected string $file;
    protected ?string $updateFile = null;
    protected ?string $updateVer = null;

    protected PendingRequest $request;
    protected ?TransferStats $stats = null;
    protected ?Response $response = null;
    protected bool $initalised = false;

    public function __construct(
        #[Config('app.version')] protected string $ver,
        #[Config('app.version_pattern')] protected string $verPattern,
        #[Config('app.update.strategy')] protected string $strategy,
        #[Config('app.update.url')] protected string $url,
        #[Config('app.update.auto')] protected bool $auto,
        #[Config('app.update.version.url')] protected string $updateVerUrl,
        #[Config('app.update.version.headers')] protected array $updateVerHeaders,
        #[Config('app.update.version.pattern')] protected string $updateVerPattern,
    )
    {
        $this->strategy = Str::upper($this->strategy);
        $this->file = \Phar::running(false);

        parent::__construct();
    }

    protected function logException(\Exception $e): void
    {
        $log['app'] = [
            'version' => $this->ver,
            'updateVersion' => $this->updateVer,
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

    protected function httpGet(string $url, string $sink = ''): bool
    {
        $this->request = Http::timeout(config('app.update.timeout', 60))
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->withOptions([
                // Avoid decoupling the instances
                'on_stats' => function (TransferStats $transferStats) use(&$stats) {
                    $stats = $transferStats;
                }
            ])
            ->when(! blank($sink), fn (PendingRequest $request) => $request->sink($sink));

        try {
            $this->response = $this->request->get($url);
        } catch (\Exception $e){
            $this->stats = $stats;
            $this->logException($e);
            return false;
        }

        $this->stats = $stats;
        Log::notice("Http get to {$url} successful." . (! blank($sink) ? " File saved to {$sink}" : ''));

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

    protected function getUpdateFile(...$params): string
    {
        $updateFile = join_paths(...$params);

        if (File::exists($updateFile)){
            File::delete($updateFile);
            Log::notice("Existing update file removed: {$updateFile}");
        }

        $this->initalised = true;

        return $updateFile;
    }

    protected function getVer(string $verInfo, string $pattern): string
    {
        preg_match($pattern, $verInfo, $verSegments);
        if (! Arr::has($verSegments, ['major', 'minor', 'patch'])) {
            Log::error("Version could not be parsed: {$verInfo}");
            return '';
        }

        return "{$verSegments['major']}.{$verSegments['minor']}.{$verSegments['patch']}";
    }

    public function handle(): void
    {
        if ($this->option('assume-ver') !== null){
            $this->ver = $this->option('assume-ver');
        }

        $this->ver = $this->getVer($this->ver, $this->verPattern);

        if (blank($this->ver)){
            return;
        }

        $invalid = false;
        if ($invalid = ! in_array($this->strategy, ['DIRECT', 'GITHUB_RELEASE'])) {
            Log::warning("Invalid update strategy: {$this->strategy}");
        }

        if ($invalid = ! Str::isUrl($this->url)){
            Log::warning("Invalid update URL: {$this->url}");
        }

        if ($invalid){
            return;
        }

        if ($this->strategy == 'DIRECT'){
            if ($this->httpGet($this->updateVerUrl)){
                return;
            }

            $versionInfo = $this->response->body();
        }elseif ($this->strategy == 'GITHUB_RELEASE'){
            try {
                $latest = GitHub::releaseLatest($this->url);
            }catch (\Exception $e){
                $this->logException($e);
                return;
            }

            if ($latest === null){
                Log::warning("No release found in GitHub repository: {$this->url}");
                return;
            }

            $versionInfo = $latest['tag_name'];
        }else {
            Log::warning("Update strategy not implemented: {$this->strategy}");
            return;
        }

        preg_match($this->updateVerPattern, $versionInfo, $updateVerSegments);
        if (! Arr::has($updateVerSegments, ['major', 'minor', 'patch'])) {
            Log::warning("Version could not be parsed: {$versionInfo}");
            return;
        }

        $this->updateVer = $this->getVer($versionInfo, $this->updateVerPattern);

        if (blank($this->updateVer)){
            return;
        }

        if (version_compare($this->updateVer, $this->ver, '<=')) {
            Log::notice("No update available. Current version: {$this->ver}, Next version: {$this->updateVer}");
            return;
        }

        $this->updateFile = $this->getUpdateFile(dirname($this->file), 'ntrn_update');

        if (! $this->httpGet($this->url, $this->updateFile)){
            return;
        }

        File::chmod($this->updateFile, fileperms($this->file));
        Log::notice('Update file permissions set.');

        try {
            if ($this->option('no-cleanup')){
                File::copy($this->updateFile, $this->file);
            }else {
                File::move($this->updateFile, $this->file);
            }

        } catch (\Exception $e){
            $this->logException($e);
            return;
        }

        Log::notice("App update from {$this->ver} to {$this->updateVer} successful.");
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

        if (! $this->option('no-cleanup') && ! blank($this->updateFile) && File::exists($this->updateFile)){
            File::deleteDirectory($this->updateFile);
        }
    }
}
