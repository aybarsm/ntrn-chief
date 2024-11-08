<?php

namespace App\Commands;

use App\Actions\AppUpdateDirect;
use App\Actions\AppUpdateGitHubRelease;
use App\Framework\Commands\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Str;

class AppUpdate extends Command
{
    protected $signature = 'app:update
    {--assume-ver= : Assume the current version}
    {--no-cleanup : Do not cleanup temporary files}';

    protected $description = 'Update the application';

    protected bool $initalised = false;

    public function handle(
        #[Config('app.update.strategy')] string $strategy,
        #[Config('app.update.auto')] bool $auto,
    ): void
    {
        $strategy = Str::upper($strategy);

        if ($strategy == 'GITHUB_RELEASE'){
            $this->app->call(AppUpdateGitHubRelease::class);
        }elseif ($strategy == 'DIRECT') {
            $this->app->call(AppUpdateDirect::class);
        }else {
            $this->error("Invalid update strategy [{$strategy}]");
        }
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->hourly();
    }

    public function __destruct()
    {
//        if (! $this->initalised) {
//            return;
//        }
//
//        if (! $this->option('no-cleanup') && ! blank($this->updateFile) && File::exists($this->updateFile)) {
//            File::deleteDirectory($this->updateFile);
//        }
    }
}
