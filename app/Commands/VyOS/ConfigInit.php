<?php

declare(strict_types=1);

namespace App\Commands\VyOS;

use App\Framework\Commands\Command;
use App\Services\VyOs;
use Illuminate\Support\Facades\Process;

class ConfigInit extends Command
{
    protected $signature = 'vyos:config-init';

    protected $description = 'Initialise the VyOS configuration';

    public function handle()
    {

//        $cmd = 'show configuration json';
//        $process = Process::env(['SHELL' => '/bin/vbash'])->run($cmd);
        $process = Process::tty(true)->run('echo "$SHELL"; echo "-----------------------"; env; echo "-----------------------"; alias');
        $this->comment('Exit Code: '.$process->exitCode());
        $this->comment('Output: '.$process->output());
        $this->comment('Error Output: '.$process->errorOutput());
//        if ($process->failed()) {
//            $this->error('Failed to retrieve VyOS configuration');
//            $this->comment('Exit Code: '.$process->exitCode());
//            $this->comment('Output: '.$process->output());
//            $this->comment('Error Output: '.$process->errorOutput());
//            return;
//        }
        //        $config = VyOs::getConfig();
        //        dump($config);
    }
}
