<?php

declare(strict_types=1);

namespace App\Commands\VyOS;

use App\Enums\VyOSConfig;
use App\Framework\Commands\Command;
use App\Services\VyOs;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Process as SymfonyProcess;
class ConfigInit extends Command
{
    protected $signature = 'vyos:config-init';

    protected $description = 'Initialise the VyOS configuration';

    public function handle(): void
    {
//        $config = VyOs::getConfig(VyOSConfig::ARRAY);
//        $this->line(json_encode($config, JSON_PRETTY_PRINT));
    }
}
