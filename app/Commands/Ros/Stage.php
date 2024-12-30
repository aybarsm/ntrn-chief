<?php

declare(strict_types=1);

namespace App\Commands\Ros;

use App\Framework\Commands\Command;

class Stage extends Command
{
    protected $signature = 'ros:stage
    {stage : The stage to run}';

    protected $description = 'Run a VyOS stage';

    public function handle()
    {
        //
    }
}
