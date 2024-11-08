<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
#[CommandTask('gatherBuilds', null, 'Gathering Builds')]
#[CommandTask('selectBuild', null, 'Select Build to Distribute')]
class AppDistribute extends TaskingCommand
{
    protected $signature = 'app:app-distribute';
    protected $description = 'Command description';
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

    protected function selectBuild(): bool
    {

    }

    public function handle()
    {
        $this->executeTasks();
    }
}
