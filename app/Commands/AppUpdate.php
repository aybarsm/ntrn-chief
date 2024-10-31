<?php

namespace App\Commands;

use App\Services\Helper;
use App\Traits\Configable;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;

class AppUpdate extends Command
{
    use Configable;
    protected $signature = 'app:update';
    protected $description = 'Update the application';
    protected PendingRequest $request;
    protected ?TransferStats $stats = null;
    protected ?Response $response = null;

    protected function logException(\Exception $e): void
    {
//        Log::withContext([
//            'exception' => $e,
//            'request' => $this->request,
//            'stats' => $this->stats,
//            'response' => $this->response
//        ]);

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

    public function handle(): void
    {
        if (! Helper::isPhar()){
            return;
        }

        [$verCur, $verTrg] = [app()->version(), 'v0.0.4'];

        if (version_compare($verTrg, $verCur, '<=')) {
            return;
        }

        $tempFile = Helper::tempFile(false, true, "ntrn_{$verTrg}", '');
        Log::info("Downloading update from {$verCur} to {$verTrg} to {$tempFile}");
        $url = 'http://localhost:8000/download.php?wait=0&slow=0';

        $stats = null;
        $this->request = Http::sink($tempFile)
            ->timeout(config('app.update.timeout', 60))
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->withOptions([
                'on_stats' => function (TransferStats $transferStats) use(&$stats) {
                    $stats = $transferStats;
                }
            ]);

        try {
            $this->response = $this->request->get($url);
            $this->stats = $stats;
        } catch (\Exception $e){
            $this->stats = $stats;
            $this->logException($e);
            return;
        }

        if ($this->response->successful()) {
            $runningPhar = \Phar::running(false);
            File::move($tempFile, $runningPhar);
            File::delete($tempFile);
            Log::notice("App update from {$verCur} to {$verTrg} successful.");
        }
    }

    public function schedule(Schedule $schedule): void
    {
        if (! config('app.update.auto', false)) {
            return;
        }

         $schedule->command(static::class)->hourly();
    }
}
