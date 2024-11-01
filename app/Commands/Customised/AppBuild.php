<?php

namespace App\Commands\Customised;

use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Prompts\Progress;
use App\Services\Archive;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use function Illuminate\Filesystem\join_paths;

use App\Attributes\Console\CommandTask;

#[CommandTask('setParameters', null, 'Set build parameters', true)]
#[CommandTask('prepare', null, 'Prepare compile environment', true)]
#[CommandTask('compile', IndicatorType::SPINNER, 'Compile .phar file', true)]
#[CommandTask('checkDistributions', IndicatorType::SPINNER, 'Check distributions', false, true)]
#[CommandTask('downloadSfx', IndicatorType::PROGRESS, 'Download Micro Sfx')]
#[CommandTask('extractSfx', IndicatorType::SPINNER, 'Extract Micro Sfx')]
#[CommandTask('buildBinaries', IndicatorType::SPINNER, 'Build distribution binaries')]
class AppBuild extends TaskingCommand
{
    use Configable;

    protected string $configablePrefix = 'build';
    protected bool $initalised = false;

    protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}
    {--leave-initial : Don\'t rename/move the initial .phar file after compiling}
    {--no-distributions : Skip building distributions}';

    protected $description = 'Build a single file executable (Customised)';

    public function handle(): void
    {
        $this->setSignalHandler('SIGINT', function () {
            $this->cleanUp(true);
        });

        $this->executeTasks();
    }

    protected function restoreBackups(): void
    {
        $buildId = $this->config('get', 'id', 'Unknown');
        $historyPath = $this->config('get', 'backup.history');
        if (! File::exists($historyPath)){
            return;
        }

        $history = collect(File::json($historyPath))->sortByDesc('ts')->toArray();
        $newHistory = $history;

        foreach($history as $histKey => $backup){
            if (File::exists($backup['src'])){
                Log::info("Build [{$buildId}] : Backup restore skipped. Backup destination (Source) already exists.", $backup);
                continue;
            }elseif (! File::exists($backup['dest'])){
                Log::info("Build [{$buildId}] : Backup restore skipped. Backup source (Destination) does not exist.", $backup);
                continue;
            }

            $result = $backup['isDir'] ? File::moveDirectory($backup['dest'], $backup['src']) : File::move($backup['dest'], $backup['src']);

            if ($result) {
                unset($newHistory[$histKey]);
                File::put($historyPath, json_encode(array_values($newHistory), JSON_PRETTY_PRINT));
                Log::info("Build [{$buildId}] : Backup restored.", $backup);
            }else {
                Log::error("Build [{$buildId}] : Backup restore failed.", $backup);
            }
        }
    }

    protected function backup(string $historyPath, string $src, string $dest, string $ts, string $buildId): bool
    {
        if (! File::exists($src)){
            $this->setTaskMessage("<error>Source file/directory does not exist at {$src}</error>");
            return false;
        }

        if (! File::exists($historyPath)){
            File::ensureDirectoryExists(dirname($historyPath));
            File::put($historyPath, '[]');
            $history = [];
        }else {
            $history = File::json($historyPath);
        }

        File::ensureDirectoryExists(dirname($dest));
        $result = $isDir = File::isDirectory($src) ? File::moveDirectory($src, $dest) : File::move($src, $dest);

        if ($result){
            $history[] = ['build' => $buildId, 'ts' => $ts, 'src' => $src, 'dest' => $dest, 'isDir' => File::isDirectory($src)];
            File::put($historyPath, json_encode($history, JSON_PRETTY_PRINT));
            $this->setTaskMessage("<info>Backup of {$src} to {$dest} was successful.</info>");
        }else {
            $this->setTaskMessage("<error>Backup of {$src} to {$dest} failed.</error>");
        }

        return $result;
    }

    protected function setParameters(): bool
    {
        $config = [];
        $config['ts.instance'] = Carbon::now('UTC');
        $config['ts.safe'] = $config['ts.instance']->format('Ymd\THis\Z');
        $config['name'] = Str::lower(config('app.name'));
        $config['version'] = config('app.version');
        $config['backup.path'] = joinPaths(config('dev.build.backup.path'), "{$config['version']}-{$config['ts.safe']}");
        $config['backup.history'] =  joinPaths(config('dev.build.backup.path'), 'history.json');

        foreach(config('dev.build.exclude', []) as $excludeKey => $exclude){
            $excBase = Str::of($exclude)
                ->after(base_path())
                ->split('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', -1, PREG_SPLIT_NO_EMPTY)
                ->toArray();
            $config["backup.items.{$excludeKey}"] = ['src' => $exclude, 'dest' => join_paths($config['backup.path'], ...$excBase)];
        }

        if ($this->config('get', 'version') === 'unreleased') {
            $this->setTaskMessage('<error>App has not released yet.</error>');
            return false;
        }

        $config['initial'] = join_paths(base_path(), "{$this->getBinary()}.phar");
        $config['id'] = "{$config['version']}-{$config['ts.safe']}";
        $config['path'] = join_paths(config('dev.build.path'), $config['id']);
        $config['phar'] = join_paths($config['path'], "{$config['name']}.phar");

        $config['box.binary'] = join_paths(base_path(), 'vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box'));

        if ($this->option('no-distributions')){
            $this->setTaskMessage("<comment>Skipping distributions.</comment>");
            $this->configables['build'] = Arr::undot($config);
            return true;
        }

        $microPath = config('dev.build.micro.path');
        $microUrl = Str::of(config('dev.build.micro.url'))->trim()->finish('/');
        $microArchivePattern = config('dev.build.micro.archivePattern', '');

        foreach (config('dev.build.micro.distributions', []) as $distribution => $micro) {
            $cnfKey = 'distributions.' . Str::replace('.', '_', $distribution);

            $micro['remote'] = Str::of($micro['remote'])->trim()->ltrim('/')->value();

            $config["{$cnfKey}.target"] = $distribution;
            $config["{$cnfKey}.output"] = join_paths($config['path'], "{$config['name']}_{$distribution}");
            $config["{$cnfKey}.sfx.local"] = join_paths($microPath, $micro['local']);
            $config["{$cnfKey}.sfx.localExists"] = File::exists($config["{$cnfKey}.sfx.local"]);
            $config["{$cnfKey}.sfx.remote"] = $microUrl->finish($micro['remote'])->value();
            $config["{$cnfKey}.sfx.remoteArchive"] = ! blank($microArchivePattern) && Str::isMatch($microArchivePattern, $config["{$cnfKey}.sfx.remote"]);

            $downloadSuffix = Str::of($micro['remote'])->start('downloads/')->split('/\//', -1, PREG_SPLIT_NO_EMPTY)->toArray();
            $downloadArchivePath = join_paths(dirname($config["{$cnfKey}.sfx.local"]), ...$downloadSuffix);
            $downloadPath = $config["{$cnfKey}.sfx.remoteArchive"] ? $downloadArchivePath : $config["{$cnfKey}.sfx.local"];

            $config["{$cnfKey}.sfx.downloadPath"] = $downloadPath;
            $config["{$cnfKey}.sfx.downloadExists"] = File::exists($downloadPath);

            $config["{$cnfKey}.sfx.archiveFile"] = blank($micro['archiveFile']) ? 'micro.sfx' : $micro['archiveFile'];
            $config["{$cnfKey}.sfx.extractDir"] = join_paths(config('dev.temp'), Str::uuid());
        }

        $this->configables['build'] = Arr::undot($config);

        return true;
    }

    protected function prepare(): bool
    {
        $this->initalised = true;
        $backupItems = $this->config('get', 'backup.items', []);

        if (! blank($backupItems)){
            $ts = $this->config('get', 'ts.instance')->toIso8601ZuluString();
            $buildId = $this->config('get', 'id');
            $historyPath = $this->config('get', 'backup.history');

            foreach($backupItems as $item){
                if (! $this->backup($historyPath, $item['src'], $item['dest'], $ts, $buildId)){
                    return false;
                }
            }
        }

        $initial = $this->config('get', 'initial');
        $boxDump = join_paths(dirname($initial), '.box_dump');

        if (File::exists($boxDump) && File::isDirectory($boxDump)){
            File::deleteDirectory($boxDump);
            $this->setTaskMessage("<comment>Old .box_dump deleted.</comment>");
        }

        if (File::exists($initial)){
            File::delete($initial);
            $this->setTaskMessage("<comment>Old initial .phar file deleted.</comment>");
        }

        File::put(config('dev.build.app_version', config_path('app_version')), $this->config('get', 'version'));
        $this->setTaskMessage("<comment>app_version file created.</comment>");

        File::put(config('dev.build.app_build', config_path('app_build')), $this->config('get', 'ts.safe'));
        $this->setTaskMessage("<comment>app_build file created.</comment>");

        foreach(config('dev.build.exclude', []) as $excludeKey => $exclude){
            $this->setTaskMessage("<comment>Excluding: {$exclude}</comment>");
        }

        return true;
    }

    protected function compile(): bool
    {
        $initial = $this->config('get', 'initial');
        $phar = $this->config('get', 'phar');

        $process = Process::timeout($this->getTimeout())
            ->command([$this->config('get', 'box.binary'), 'compile'] + $this->getBoxOptions())
            ->run();

        $initFile = File::exists($initial);

        if ($process->successful()){
            if ($initFile) {
                $actualOutput = $this->option('leave-initial') ? $initial : $phar;
                if (! $this->option('leave-initial')) {
                    File::ensureDirectoryExists(dirname($phar));
                    File::move($initial, $phar);
                }
                $this->setTaskMessage("<info>Compile was successful and output is at {$actualOutput}</info>");
            }else {
                $this->setTaskMessage("<comment>Compile was successful but initial output does not exist at {$initial}</comment>");
            }
        }else {
            $this->setTaskMessage("<error>Failed to compile the application. Exit Code: {$process->exitCode()}</error>");
        }

        return $process->successful() && $initFile;
    }

    protected function checkDistributions(): bool|null
    {
        $distributions = $this->config('get', 'distributions', []);
        if (count($distributions) == 0){
            $this->setTaskMessage("<comment>No distributions to check.</comment>");
            return null;
        }

        $this->taskMessageTitle = ' | Distributions to be built:';
        foreach(array_keys($distributions) as $key => $distribution){
            $order = $key + 1;
            $this->setTaskMessage("<info>#{$order}: {$distribution}</info>");
        }

        return true;
    }

    protected function downloadSfx(): bool
    {
        $distributions = $this->config('get', 'distributions', []);

        foreach($distributions as $distribution){
            $sfx = $distribution['sfx'];
            if ($sfx['localExists']){
                $this->setTaskMessage("<comment>{$distribution['target']} Sfx already exists locally at {$sfx['local']}</comment>");
                continue;
            }elseif ($sfx['downloadExists']){
                $this->setTaskMessage("<comment>{$distribution['target']} Sfx already downloaded at {$sfx['downloadPath']}</comment>");
                continue;
            }

            File::ensureDirectoryExists(dirname($sfx['downloadPath']));
            $this->indicator = new Progress(steps: 0);
            $this->indicator = Helper::downloadProgress($this->indicator, $sfx['remote'], "for {$distribution['target']}");
            $this->indicator->config('set', 'auto.finish', true);
            $this->indicator->config('set', 'auto.clear', true);
            $this->indicator->config('set', 'show.finish', 2);

            $response = Http::sink($sfx['downloadPath'])->withOptions([
                'progress' => function ($dlSize, $dlCompleted) {
                    $this->indicator->progress($dlCompleted);
                },
                'on_headers' => function (ResponseInterface $response) use($sfx) {
                    $this->indicator->total((int)$response->getHeaderLine('Content-Length'));
                }
            ])->get($sfx['remote']);

            if ($response->successful()) {
                $this->setTaskMessage("<info>{$distribution['target']} Sfx downloaded successfully to {$sfx['downloadPath']}</info>");
            }else {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx download failed.</error>");
            }
        }

        return true;
    }
    protected function extractSfx(): bool
    {
        $distributions = $this->config('get', 'distributions', []);

        foreach($distributions as $distribution){
            $sfx = $distribution['sfx'];

            if ($sfx['localExists'] || ! $sfx['remoteArchive']){
                continue;
            }

            if (! File::exists($sfx['downloadPath'])){
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive does not exist at {$sfx['downloadPath']}</error>");
                continue;
            }

            File::ensureDirectoryExists($sfx['extractDir']);

            $result = false;
            try{
                Archive::extractTo($sfx['downloadPath'], $sfx['extractDir']);
                $result = true;
            }catch (\Throwable $exception){
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$sfx['downloadPath']}</error>");
                $this->setTaskMessage("<error>Exception Message: {$exception->getMessage()}</error>");
            }

            if (! $result){
                continue;
            }

            $extractedSfx = join_paths($sfx['extractDir'], $sfx['archiveFile']);

            if (! File::exists($extractedSfx)){
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$extractedSfx}</error>");
            }else {
                File::ensureDirectoryExists(dirname($sfx['local']));
                File::move($extractedSfx, $sfx['local']);
                $this->config('set', "distributions.{$distribution['target']}.sfx.localExists", true);
                $this->setTaskMessage("<info>{$distribution['target']} Sfx archive extracted successfully to {$sfx['local']}</info>");
            }
        }

        return true;
    }

    protected function buildBinaries(): bool
    {
        $distributions = $this->config('get', 'distributions');
        $pharKey = $this->option('leave-initial') ? 'initial' : 'phar';
        $phar =  $this->config('get', $pharKey);

        foreach($distributions as $distribution) {
            $sfx = $distribution['sfx'];

            if (! $sfx['localExists']){
                $this->setTaskMessage("<error>{$distribution['target']} Sfx does not exist at {$sfx['local']}</error>");
                continue;
            }

            $output = $distribution['output'];
            File::ensureDirectoryExists(dirname($output));

            $process = Process::timeout($this->getTimeout())->command("cat {$sfx['local']}  {$phar} > {$output}")->run();
            if ($process->successful()){
                $this->setTaskMessage("<info>{$distribution['target']} built successfully at {$output}</info>");
                if (! blank($chmod = config('dev.build.chmod')) && is_string($chmod) && is_numeric($chmod) && strlen($chmod) === 4) {
                    File::chmod($output, octdec(config('dev.build.chmod')));
                }
            }else {
                $this->setTaskMessage("<error>{$distribution['target']} build failed. Exit Code: {$process->exitCode()}</error>");
                if (File::exists($output)){
                    File::delete($output);
                }
            }
        }

        return true;
    }

    private function getBinary(): string
    {
        return str_replace(["'", '"'], '', Artisan::artisanBinary());
    }

    private function getTimeout(): ?float
    {
        if (! is_numeric($this->option('timeout'))) {
            throw new \InvalidArgumentException('The timeout value must be a number.');
        }

        $timeout = (float) $this->option('timeout');

        return $timeout > 0 ? $timeout : null;
    }

    private function getBoxOptions(): array
    {
        $boxOptions = [];
        foreach($this->option('box') as $option){
            $option = Str::of($option)->trim();
            $boxOptions[$option->ltrim('-')->before('=')->value()] = $option->after('=')->value();
        }

        $boxOptions = array_merge($boxOptions, [
            'working-dir' => base_path(),
            'config' => base_path('box.json'),
        ]);

        if ($this->output->isDebug()) {
            $boxOptions['debug'] = '';
        }

        return array_values(Arr::map($boxOptions,
            fn($value, $key) => Str::of($key)->start('--')->unless(blank($value), fn ($str) => $str->append('=' . $value))->value()
        ));
    }
    private function cleanUp(bool $isSignal = false): AppBuild|int
    {
        if (! $this->initalised){
            return $isSignal ? self::SUCCESS : $this;
        }

        $files = [
            config('dev.build.app_version', config_path('app_version')),
            config('dev.build.app_build', config_path('app_build')),
        ];

        foreach($files as $file){
            if (File::exists($file)){
                File::delete($file);
            }
        }

        foreach($this->config('get', 'distributions', []) as $distribution){
            if (File::exists($distribution['sfx']['extractDir'])){
                File::deleteDirectory($distribution['sfx']['extractDir']);
            }
        }

        $this->restoreBackups();

        return $isSignal ? self::SUCCESS : $this;
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
