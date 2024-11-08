<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Framework\Commands\TaskingCommand;
use App\Services\GitHub\Contracts\GitHubContract;
use App\Traits\Configable;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParams', null, 'Set Parameters')]
#[CommandTask('gatherBuilds', null, 'Gathering Builds')]
class AppRelease extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:release';

    protected $description = 'Release the built application';

    protected ?PendingProcess $client;

    protected function setParams(): bool
    {
        $this->client = app(GitHubContract::class)->getDevClient();

        return true;
    }

    protected function gatherBuilds(): bool
    {
        $builds = collect(File::directories(config('dev.build.path')))
            ->map(function ($item) {
                return basename($item);
            })
            ->filter(function ($item) {
                return Str::isMatch('/^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $item);
            })
            ->sortDesc()
            ->values();

        if ($builds->isEmpty()) {
            $this->setTaskMessage('<error>No builds found</error>');

            return false;
        }

        $this->config('set', 'builds', $builds->toArray());

        return true;
    }

    public function handle()
    {
        $this->executeTasks();
        dump($this->config('get', 'builds'));
    }
}
