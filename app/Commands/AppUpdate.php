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

class AppUpdate extends Command
{
    protected $signature = 'app:update
    {--no-cleanup : Do not cleanup temporary files}
    {--no-apply : Do not apply the update}
    {--f|force : Force update}';

    protected $description = 'Update the application';

    protected string $strategy;

    protected bool $auto;

    protected string $file;

    protected string $updateFile;

    protected bool $initalised = false;

    protected bool $force = false;

    public function handle(
        #[Config('app.update.strategy')] string $strategy,
        #[Config('app.update.auto')] bool $auto,
    ): void {
        if (! $this->app->isPhar()) {
            $this->error('This command can only be run on a built application.');

            return;
        }
        $this->auto = $auto;
        if (! $this->auto) {
            $this->warn('App auto update is disabled');
            Log::warning('App auto update is disabled');

            return;
        }

        $params = [
            'force' => $this->option('force') === true,
        ];

        $this->strategy = Str::upper($strategy);

        if ($this->strategy == 'GITHUB_RELEASE') {
            $this->app->call(AppUpdateGitHubRelease::class, $params);
        } elseif ($this->strategy == 'DIRECT') {
            $this->app->call(AppUpdateDirect::class, $params);
        } else {
            Log::error("Invalid update strategy [{$strategy}]");

            return;
        }

        if (! Context::has('appUpdateRequired')) {
            Log::error('Update requirement has not found in the context');

            return;
        }

        if ($this->option('force') !== true && ! Context::get('appUpdateRequired')) {
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
