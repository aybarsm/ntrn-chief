<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

#[CommandTask('checkParams', null, 'Checking parameters')]
#[CommandTask('gatherBuilds', null, 'Gathering builds')]
class AppPublish extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:publish';
    protected $description = 'Publish the built application';

    protected function checkParams(): bool
    {
        if (config('dev.github.url') === null){
            $this->setTaskMessage('<error>Github url not set</error>');
            return false;
        }

        if (Str::isUrl(config('dev.github.url')) === false){
            $this->setTaskMessage('<error>Github url is not a valid url</error>');
            return false;
        }

        if (config('dev.github.token') === null){
            $this->setTaskMessage('<error>Github token not set</error>');
            return false;
        }

        $remotes = Helper::gitRemote();
        if ($remotes->isEmpty()) {
            $this->setTaskMessage('<error>No git remotes found</error>');
            return false;
        }

        return false;
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
            ->sortDesc();

        if ($builds->isEmpty()) {
            $this->setTaskMessage('<error>No builds found</error>');
            return false;
        }

        $this->config('set','builds', $builds->toArray());



        return true;
    }

    public function handle()
    {
        $this->executeTasks();
    }
}
