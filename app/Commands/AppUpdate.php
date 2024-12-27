<?php

namespace App\Commands;

use App\Actions\AppUpdateDirect;
use App\Actions\AppUpdateGitHubRelease;
use App\Framework\Commands\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

//TODO: Force update
class AppUpdate extends Command
{
    protected $signature = 'app:update
    {--assume-ver= : Assume the current version}
    {--no-cleanup : Do not cleanup temporary files}
    {--no-apply : Do not apply the update}';

    protected $description = 'Update the application';

    protected string $strategy;

    protected bool $auto;

    protected string $file;

    protected string $updateFile;

    protected bool $initalised = false;

    public function handle(
        #[Config('app.update.strategy')] string $strategy,
        #[Config('app.update.auto')] bool $auto,
    ): void {
        $this->auto = $auto;
        if (! $this->auto) {
            Log::warning('App auto update is disabled');

            return;
        }

        if ($this->option('assume-ver') !== null) {
            Context::add('debugAppUpdateAssumeVer', $this->option('assume-ver'));
        }

        $this->strategy = Str::upper($strategy);

        if ($this->strategy == 'GITHUB_RELEASE') {
            $this->app->call(AppUpdateGitHubRelease::class);
        } elseif ($this->strategy == 'DIRECT') {
            $this->app->call(AppUpdateDirect::class);
        } else {
            Log::error("Invalid update strategy [{$strategy}]");

            return;
        }

        if (! Context::has('appUpdateRequired')) {
            Log::error('Update requirement has not found in the context');

            return;
        }

        if (! Context::get('appUpdateRequired')) {
            Log::info('No update required');

            return;
        }

        if (! Context::has('appUpdateFile')) {
            Log::error('Update file path has not found in the context');

            return;
        }

        $this->file = \Phar::running(false);
        $this->updateFile = Context::get('appUpdateFile');
        $context = ['file' => $this->file, 'updateFile' => $this->updateFile];

        if ($this->option('no-apply')) {
            Log::info('Update file ready but not applied', $context);

            return;
        }

        File::chmod($this->updateFile, fileperms($this->file));
        Log::info('Update file permissions updated', array_merge($context, ['perms' => fileperms($this->file)]));

        if ($this->option('no-cleanup')) {
            File::copy($this->updateFile, $this->file);
            Log::info('Update file copied', $context);
        } else {
            File::move($this->updateFile, $this->file);
            Log::info('Update file moved', $context);
        }
        $this->info('Application updated');
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->hourly();
    }
}
