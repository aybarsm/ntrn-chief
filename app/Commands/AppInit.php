<?php

declare(strict_types=1);

namespace App\Commands;

use App\Framework\Commands\Command;
use App\Services\Helper;
use Illuminate\Support\Facades\File;

class AppInit extends Command
{
    protected $signature = 'app:init';

    protected $description = 'Initialise the application';

    public function handle(): void
    {
        $path = fluent([]);
        $path->set('env.dest', $this->app->environmentFilePath());

        if (Helper::appIsRos()) {
            $this->comment('Router OS detected');
            $path->set('env.src', resource_path('env/env.ros.example'));
            $path->set('bin.dest', '/usr/local/bin/ntrn');
            $path->set('bin.src', resource_path('files/ros/ntrn'));

            if (File::exists($path->get('bin.dest'))) {
                $this->comment('Ros binary already exists');
            } elseif (File::exists($path->get('bin.src'))) {
                $bin_copy = File::copy($path->get('bin.src'), $path->get('bin.dest'));
                if ($bin_copy) {
                    $this->info('Ros binary copied successfully');
                    File::chmod($path->get('bin.dest'), 0755);
                }
            }
        } else {
            $path->set('env.src', resource_path('env/env.example'));
        }

        if (File::exists($path->get('env.dest'))) {
            $this->comment('Environment file already exists');
        } else {
            $result = File::copy($path->get('env.src'), $path->get('env.dest'));
            if ($result) {
                $this->info('Environment file created successfully');
            } else {
                $this->error('Failed to create environment file');
            }
        }

        $confirmKeyGen = $this->prompt('confirm', 'Generate application key?', 'yes')->prompt();
        if ($confirmKeyGen) {
            $this->call('key:generate');
        }
    }
}
