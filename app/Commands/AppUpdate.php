<?php

namespace App\Commands;

use App\Traits\Configable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class AppUpdate extends Command
{
    use Configable;
    protected $signature = 'app:update';
    protected $description = 'Update the application';

    public function handle(): void
    {
        if (! config('app.auto.update', false)) {
            return;
        }
        
        $this->config('set', 'composer', File::json(base_path('composer.json')));

    }

    public function schedule(Schedule $schedule): void
    {
         $schedule->command(static::class)->hourly();
    }
}
