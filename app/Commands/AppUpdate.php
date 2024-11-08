<?php

namespace App\Commands;

use App\Framework\Commands\Command;
use App\Traits\Configable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Container\Attributes\Config;

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
