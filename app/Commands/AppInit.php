<?php

declare(strict_types=1);

namespace App\Commands;

use App\Framework\Commands\Command;
use App\Services\Helper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class AppInit extends Command
{
    protected $signature = 'app:init';

    protected $description = 'Initialise the application';

    public function handle(): void
    {
        $path = $this->app->environmentFilePath();
        if (File::exists($path)) {
            $this->error('Environment file already exists');

            return;
        }

        $source = '';
        if (Helper::appIsVyOS()) {
            $source = resource_path('env/env.ros.example');
        }

        $source = blank($source) ? resource_path('env/env.example') : $source;

        $result = File::copy($source, $path);
        if ($result) {
            $this->info('Environment file created successfully');
        } else {
            $this->error('Failed to create environment file');
        }

        $confirmKeyGen = $this->prompt('confirm', 'Generate application key?', 'yes')->prompt();
        if ($confirmKeyGen) {
            $this->call('key:generate');
        }
    }
}
