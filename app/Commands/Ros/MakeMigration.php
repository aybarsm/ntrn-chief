<?php

declare(strict_types=1);

namespace App\Commands\Ros;

use App\Framework\Commands\Command;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class MakeMigration extends Command
{
    protected $signature = 'ros:make-migration';
    protected $description = 'Create a new migration file';

    public function handle()
    {
        Event::Listen(function (CommandFinished $event) {
            if ($event->command !== 'make:view') {
                return;
            }

            Log::debug('CommandFinished', [
                'command' => $event->command,
                'exitCode' => $event->exitCode,
                'output' => $event->output->fetch(),
            ]);
        });

        $path = base_path('dev/resources/migrations/ros');

        Artisan::call('make:migration', [
            'name' => 'vyos_config',
            '--path' => $path,
        ]);

//        $res = $this->call('make:migration', [
//            'name' => 'vyos_config',
//            '--path' => $path,
//        ]);

//        $res = Artisan::call('make:migration', [
//            'name' => 'vyos_config',
//            '--path' => $path,
//        ]);



//        dump($res);
//        dump(Artisan::output());
//        $res = Artisan::call('make:migration', [
//            'name' => 'vyos_config',
//            '--path' => $path,
//            '--format' => 'json',
//        ]);
//
//        dump($res);
//        dump(Artisan::output());
    }
}
