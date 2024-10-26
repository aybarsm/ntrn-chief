<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;
class HttpServer extends Command
{
    protected $signature = 'http:server
    {action : Action to perform}';

    protected $description = 'Manage the HTTP server';

    public function handle(): void
    {
        $action = $this->argument('action');
        if ($action === 'start') {
            $this->startServer();
        } else {
            $this->error('Invalid action');
        }
    }

    protected function startServer(): void
    {
        run(function () {
            $server = new Server('127.0.0.1', 8002, false);
            $server->handle('/', function ($request, $response) {
                $response->end("<h1>Index</h1>");
            });
            $server->handle('/test', function ($request, $response) {
                $response->end("<h1>Test</h1>");
            });
            $server->handle('/stop', function ($request, $response) use ($server) {
                $response->end("<h1>Stop</h1>");
                $server->shutdown();
            });
            $server->start();
        });
    }
}
