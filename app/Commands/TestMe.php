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
//        $this->info(storage_path());
//        $this->info('Test Me Command: ' . App::environmentFilePath());
//        $this->info(env('NTRN_TEST_ENV', 'NOPE'));
//        $this->info(env('NTRN_DEV_ENV', 'NOPE2'));
//        dump(Env::getRepository());
//        $this->info('command end');
//        $this->line('sadasds');
//        $this->info(env('APP_KEY', 'NOPE'));
        $this->info('Version: '. config('app.version'));
    }
}
