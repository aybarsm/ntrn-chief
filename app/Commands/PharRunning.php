<?php

declare(strict_types=1);

namespace App\Commands;

use App\Framework\Commands\Command;

class PharRunning extends Command
{
    protected $signature = 'phar:running';
    protected $description = 'Command description';

    public function handle()
    {
        $phar = \Phar::running(false);
        $this->info(gettype($phar));
        dump(boolval($phar));
    }
}
