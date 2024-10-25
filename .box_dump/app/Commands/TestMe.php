<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use LaravelZero\Framework\Commands\Command;

class TestMe extends Command
{
protected $signature = 'test:me';
protected $description = 'Command description';

public function handle(): void
{








$this->info('Version: '. config('app.version'));
$this->info(resource_path('env'));
}
}
