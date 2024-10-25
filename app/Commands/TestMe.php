<?php

namespace App\Commands;

use App\Services\Helper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Brick\VarExporter\VarExporter;
class TestMe extends Command
{
    protected $signature = 'test:me';
    protected $description = 'Command description';

    public function handle(): void
    {
        $this->info($this->app->buildsPath());
//        $this->withProgressBar(rand(1, 5), function ($value) {
//            sleep(1);
//        });
//        $this->newLine();

//        $this->info(storage_path());
//        $this->info('Test Me Command: ' . App::environmentFilePath());
//        $this->info(env('NTRN_TEST_ENV', 'NOPE'));
//        $this->info(env('NTRN_DEV_ENV', 'NOPE2'));
//        dump(Env::getRepository());
//        $this->info('command end');
//        $this->line('sadasds');
//        $this->info(env('APP_KEY', 'NOPE'));
//        $this->info('Version: '. config('app.version'));
//        $this->info(resource_path('env'));
//        $configFile = $this->app->configPath('app.php');
//        $config = include $configFile;
//        dump($config);
//        dump(var_dump($config, true));
//        File::put(base_path('dev/app.php'), '<?php return '.VarExporter::export($config).';'.PHP_EOL);
//        Helper::progressBar($this->output);
    }
}
