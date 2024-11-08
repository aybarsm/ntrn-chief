<?php

namespace App\Actions;

use App\Attributes\TaskMethod;
use App\Services\GitHub\Contracts\GitHubContract;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

#[TaskMethod(method: 'setRelease', title: 'Set Relevant GitHub Release', bail: true)]
#[TaskMethod(method: 'standardiseVersions', title: 'Standardise Versions', bail: true)]
#[TaskMethod(method: 'checkUpdateRequirement', title: 'Check Update Requirement')]
#[TaskMethod(method: 'setAsset', title: 'Set Relevant Asset', bail: true)]
#[TaskMethod(method: 'downloadUpdateAsset', title: 'Download Update Asset', bail: true)]
class AppUpdateGitHubRelease extends AbstractAppUpdate
{
    protected array $release;
    protected ?array $asset;
    protected string|int $assetId;

    public function __invoke(
        #[Config('app.version')] string $appVer,
        #[Config('app.version_pattern')] string $appVerPattern,
        #[Config('app.update.version.target')] string $updateTo,
        #[Config('app.update.version.pattern')] string $updateVerPattern,
    ): void
    {
        $this->params = get_defined_vars();
        $this->executeTasks();
    }

    protected function setParameters(): void
    {
        parent::setParameters();
        $this->client = app(GitHubContract::class)->getUpdateClient();
    }

    protected function setRelease(): void
    {
        $url = 'releases' . ($this->updateTo == 'latest' ? '/latest' : "/tags/{$this->updateTo}");
        $this->release = $this->client
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->get($url)
            ->json();

        $this->updateVer = $this->release['tag_name'];
    }

    protected function setAsset(): void
    {
        throw_if(count($this->release['assets'] ?? []) == 0 , "No assets found in release [{$this->release['tag_name']}]");
        $assetSearch = config('app.update.strategies.github.release.asset', []);
        $assetSearch = Arr::where($assetSearch, fn($searchValue, $searchKey) => ! blank($searchKey) && ! blank($searchValue));

        if (count($this->release['assets']) > 1 && ! blank($assetSearch)) {
            $this->asset = Arr::first($this->release['assets'], function($asset) use ($assetSearch) {
                return count($assetSearch) == count(Arr::where($assetSearch, fn($searchValue, $searchKey) => data_get($asset, $searchKey) == $searchValue));
            });

            $assetSearch = json_encode($assetSearch);
            throw_if(blank($this->asset), "No asset found matching asset search criteria [{$assetSearch}]");
        }else {
            $this->asset = Arr::first($this->release['assets']);
        }

        $this->assetId = $this->asset['id'];
    }

    protected function downloadUpdateAsset(): void
    {
        $downloadPath = parent::generateDownloadPath();

        $this->client
            ->replaceHeaders(['Accept' => 'application/octet-stream'])
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->sink($downloadPath)
            ->get("releases/assets/{$this->assetId}");

        $this->downloadPath = $downloadPath;
    }
}
