<?php

namespace App\Actions;

use App\Attributes\TaskMethod;
use App\Services\Helper;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[TaskMethod(method: 'setParameters', title: 'Set Parameters ', bail: true)]
#[TaskMethod(method: 'queryRemoteVersion', title: 'Query Remote Version ', bail: true)]
#[TaskMethod(method: 'standardiseVersions', title: 'Standardise Versions', bail: true)]
#[TaskMethod(method: 'checkUpdateRequirement', title: 'Check Update Requirement')]
#[TaskMethod(method: 'downloadUpdateAsset', title: 'Download Update File', bail: true)]
class AppUpdateDirect extends AbstractAppUpdate
{
    protected int $updateHttpTimeout;

    protected array $updateHttpHeaders;

    protected string $updateUrl;

    protected string $updateVerUrl;

    protected int $updateVerHttpTimeout;

    protected array $updateVerHttpHeaders;

    protected PendingRequest $versionClient;

    public function __invoke(
        #[Config('app.version')] string $appVer,
        #[Config('app.version_pattern')] string $appVerPattern,
        #[Config('app.update.version.target')] string $updateTo,
        #[Config('app.update.version.pattern')] string $updateVerPattern,
        #[Config('app.update.strategies.direct.url')] string $updateUrl,
        #[Config('app.update.http.timeout')] int $updateHttpTimeout,
        #[Config('app.update.http.headers')] array $updateHttpHeaders,
        #[Config('app.update.strategies.direct.version.url')] string $updateVerUrl,
        #[Config('app.update.strategies.direct.version.http.timeout')] int $updateVerHttpTimeout,
        #[Config('app.update.strategies.direct.version.http.headers')] array $updateVerHttpHeaders,
        bool $force = false,
        ?OutputInterface $output = null,
    ): void {
        $this->params = get_defined_vars();
        $this->executeTasks();
    }

    protected function setParameters(): void
    {
        parent::setParameters();

        $client = new PendingRequest;
        $this->client = $client;
        $this->client->timeout($this->updateHttpTimeout);
        if (! blank($this->updateHttpHeaders)) {
            $this->client->withHeaders($this->updateHttpHeaders);
        }

        $this->versionClient = $client;
        $this->versionClient->timeout($this->updateVerHttpTimeout);
        if (! blank($this->updateVerHttpHeaders)) {
            $this->versionClient->withHeaders($this->updateVerHttpHeaders);
        }
    }

    protected function queryRemoteVersion(): void
    {
        $this->updateVer = $this->versionClient
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->get($this->updateVerUrl)->body();

        Log::info("Remote Version: {$this->updateVer}");
    }

    protected function downloadUpdateAsset(): void
    {
        $downloadPath = parent::generateDownloadPath();

        $progress = new \App\Prompts\Progress(steps: 0);
        $progress = Helper::downloadProgress($progress, $this->updateUrl, $downloadPath);
        $progress->config('set', 'auto.finish', true);
        $progress->config('set', 'auto.clear', true);
        $progress->config('set', 'show.finish', 2);

        $this->client
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->sink($downloadPath)
            ->withOptions([
                'progress' => function ($dlSize, $dlCompleted) use ($progress) {
                    $progress->progress($dlCompleted);
                },
                'on_headers' => function (ResponseInterface $response) use ($progress) {
                    $progress->total((int) $response->getHeaderLine('Content-Length'));
                },
            ])
            ->get($this->updateUrl);

        Log::info("Downloaded Update File: {$downloadPath}");

        $this->downloadPath = $downloadPath;
    }
}
