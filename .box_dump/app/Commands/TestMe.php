<?php

namespace App\Commands;

use App\Traits\Configurable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Brick\VarExporter\VarExporter;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\progress;
use Laravel\Prompts\Progress;
class TestMe extends Command
{
use Configurable;
protected $signature = 'test:me';
protected $description = 'Command description';

protected ?Progress $progress = null;








public function handle(): void
{

















































































































































}
}
