<?php

declare(strict_types=1);

namespace App\Commands;

use App\Framework\Commands\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

class TestScheduleSystem extends Command
{
    protected $signature = 'test:schedule-system';

    protected $description = 'Command description';

    public function handle()
    {
        Log::info('Test schedule system');
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyFiveSeconds();
    }
}
