<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Framework\Commands\TaskingCommand;
use App\Traits\Configable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters', true)]
#[CommandTask('selectOptions', null, 'Select Options to Release')]
class AppRelease extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:release';

    protected $description = 'Release the built application';

    protected array $prompts = [];

    public function handle(): void
    {
        $this->executeTasks();
    }

    protected function setParameters(): bool
    {
        $buildPath = config('dev.build.path', base_path('builds'));
        $data['builds'] = collect(File::directories($buildPath))
            ->map(function ($item) {
                return basename($item);
            })
            ->filter(function ($item) {
                return Str::isMatch('/^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $item);
            })
            ->sortDesc()
            ->map(function ($item) use ($buildPath) {
                return join_paths($buildPath, $item);
            })
            ->values()
            ->toArray();

        $this->configables = $data;

        return true;
    }

    protected function selectOptions(): bool
    {
        $builds = $this->config('get', 'builds', []);
        $releaseInfoFile = null;
        $this->prompts['build'] = $this->prompt('select',
            label: 'Select Build to Release',
            options: $builds,
            default: 0,
            validate: function ($value) use (&$releaseInfoFile) {
                $releaseInfoFile = join_paths($value, 'release.json');
                if (! File::exists($releaseInfoFile)) {
                    return "Build information file does not exist at {$releaseInfoFile}";
                }

                return null;
            },
        );

        $this->configables['release'] = $this->prompts['build']->prompt();
        $this->configables['releaseInfo'] = File::json($releaseInfoFile);

        return true;
    }
}
