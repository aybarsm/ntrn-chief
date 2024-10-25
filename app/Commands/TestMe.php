<?php

namespace App\Commands;

use App\Services\GitHub;
use App\Traits\Configurable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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

//    private OutputInterface $originalOutput;
//
//    public function run(InputInterface $input, OutputInterface $output): int
//    {
//        return parent::run($input, $this->originalOutput = $output);
//    }

    public function handle(): void
    {
        $input = 'aybarsm/ntrn-chief   ';
        $url = 'https://github.com/aybarsm/ntrn-chief/issues';
        $input = trim($input);
//        dump(Str::isMatch('/^(?<org>[^\/]+)\/(?<repo>[^\/]+)$/', $input));
        dump(GitHub::tagLatest($url));
//        dump(GitHub::resolveRepositoryFromUrl($url));
//        $this->config()->set('composer', File::json(base_path('composer.json')));
//        $this->config('set', 'composer', File::json(base_path('composer.json')));
//        dump($this->config()->full());
//        dump(version_compare(app()->version(), 'v0.0.2', '<'));
//        dump(\Composer\);
//        $local = base_path('dev/php-8.3.13-micro-macos-aarch64.sfx');
//////        $remote = 'http://localhost:8000/php-8.3.13-micro-macos-aarch64.sfx';
//        $remote = 'http://localhost:8000/download.php';
//
//        if (File::exists($local)){
//            File::delete($local);
//        }
//
//        $this->progress = null;
//
//        Http::sink($local)->withOptions([
//            'progress' => function ($dlSize, $dlCompleted) {
//                if ($this->progress === null && $dlSize > 0){
//                    $this->progress = progress('Downloading File', $dlSize);
//                }
//
////                if ($progress !== null && $dlSize > 0){
////                    $progress->start();
////                }
////
////                $progress->setProgress($dlCompleted);
////            },
////            'on_headers' => function (ResponseInterface $response) {
////                $this->progress = progress('Downloading', $response->getHeaderLine('Content-Length'));
////            }
//        ])->get($remote);

//        $download = Http::sink($local);
//        $download = Http::createPendingRequest();
//        dump($download);
//

//
//        $section = tap($this->originalOutput->section())->write('');
//
//        $progressBar = $this->output->createProgressBar();
//        $progressBar = new ProgressBar(
//            output: $this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? new NullOutput : $section
//        );

//        Http::sink($local)->withOptions([
//            'progress' => function ($dlSize, $dlCompleted, $ulSize, $ulCompleted) use($progressBar) {
//                if ($progressBar->getMaxSteps() == 0 && $dlSize > 0){
//                    $progressBar->setMaxSteps($dlSize);
//                    $progressBar->start();
//                }
//
//                if ($progressBar->getMaxSteps() > 0){
//                    if ($dlCompleted < $dlSize){
//                        $progressBar->setProgress($dlCompleted);
//                    }elseif ($progressBar->getProgress() < $progressBar->getMaxSteps() && $dlCompleted == $dlSize){
//                        $progressBar->finish();
//                        $progressBar->clear();
//                        $this->line("Completed");
//                    }
////                    if ($dlCompleted == $dlSize && $progressBar->getProgress() == $progressBar->getMaxSteps()){
////                        $progressBar->finish();
////                        $this->info('Progress: ' . $progressBar->getProgress());
//////                        $section->clear();
////                        $this->info("Completed");
////                    }else {
////                        $progressBar->setProgress($dlCompleted);
////                    }
//                }
////                $this->info("Downloaded: {$dlCompleted} / {$dlSize}");
//            }
//        ])->get($remote)->then(
//            onFulfilled: function () use ($local) {
//                $this->info("SFX file is downloaded: {$local}");
//            },
//        );;

//        $response = Http::head($remote);
//        $fileSize = (int)$response->header('Content-Length');
//
//        /** @phpstan-ignore-next-line This is an instance of `ConsoleOutputInterface` */
//        $section = tap($this->originalOutput->section())->write('');
//
//        $progressBar = new ProgressBar(
//            output: $this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? new NullOutput : $section
//        );
//
//        Http::withOptions([
//            'stream' => true,
//            'progress' => function ($dlSize, $dlCompleted, $ulSize, $ulCompleted) use ($progressBar, $section) {
//                if ($progressBar->getMaxSteps() == 0){
//                    $progressBar->setMaxSteps($dlSize);
//                    $progressBar->start();
//                }
//
//                $progressBar->setProgress($dlCompleted);
//                if ($dlCompleted == $dlSize){
//                    $progressBar->finish();
//                    $section->clear();
//                }
//            }
//        ])->sink($local)->get($remote);
//
//        $this->output->newLine();

//            ->get($remote)
//            ->then(function ($response) use ($fileHandle, $progressBar, &$downloadedBytes) {
//                $bodyStream = $response->getBody();
//                while (!$bodyStream->eof()) {
//                    $chunk = $bodyStream->read(1024 * 8); // Read in 8KB chunks
//                    fwrite($fileHandle, $chunk);
//
//                    // Update progress bar
//                    $downloadedBytes += strlen($chunk);
//                    $progressBar->setProgress($downloadedBytes);
//                }
//            });

//        fclose($fileHandle);
//        $progressBar->finish();

//        $this->info($this->app->buildsPath());
//        $this->config()->set('build.ts', Carbon::now('UTC'));
//        dump($this->config()->get('build.ts'));
//        $this->config()->set('tasks', ['task1']);
//        $this->config()->prepend('task2', 'tasks');
//
//        dump($this->config()->full());

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
