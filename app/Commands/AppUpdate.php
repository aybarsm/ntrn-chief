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
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
class AppUpdate extends Command
{
    use Configable;
    protected $signature = 'app:update';
    protected $description = 'Update the application';
    protected PendingRequest $request;
    protected ?TransferStats $stats = null;
    protected ?Response $response = null;

    protected function logException(ConnectionException|RequestException $e): void
    {
        Log::withContext([
            'exception' => $e,
            'request' => $this->request,
            'stats' => $this->stats,
            'response' => $this->response
        ]);

        $log['exception'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($this->stats){
            $log['stats'] = [
                'effectiveUri' => $this->stats->getEffectiveUri(),
                'transferTime' => $this->stats->getTransferTime(),
            ];
        }

        if ($this->response){
            $log['response'] = [
                'status' => $this->response->status(),
                'headers' => $this->response->headers(),
                'clientError' => $this->response->clientError()
            ];
        }

        Log::error();

    }

    public function handle(): void
    {

        [$verCur, $verTrg] = [app()->version(), 'v0.0.4'];

        if (version_compare($verTrg, $verCur, '>')){
            $this->info('Updating the application...');
        }else {
            $this->info('The application is up to date.');
        }

        $tempFile = Helper::tempFile(false, true, "ntrn_{$verTrg}", '');
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
        } catch (ConnectionException|RequestException $e){
            $this->logException($e);
            $this->error('failed');
            return;
        }

        if ($this->response->successful()) {
            $this->info('Downloaded successfully.');
        }

//        $this->config('set', 'composer', File::json(base_path('composer.json')));

    }

    public function schedule(Schedule $schedule): void
    {
         $schedule->command(static::class)->hourly();
    }
}
