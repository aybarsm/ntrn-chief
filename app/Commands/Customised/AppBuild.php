<?php

namespace App\Commands\Customised;

use App\Attributes\Console\CommandTask;
use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Prompts\Progress;
use App\Services\Archive;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

use function Illuminate\Filesystem\join_paths;

#[CommandTask('checkRepoStatus', null, 'Check repo status', true)]
#[CommandTask('setParameters', null, 'Set build parameters', true)]
#[CommandTask('prepare', null, 'Prepare compile environment', true)]
#[CommandTask('compile', IndicatorType::SPINNER, 'Compile .phar file', true)]
#[CommandTask('checkDistributions', null, 'Check distributions', false, true)]
#[CommandTask('downloadSfx', IndicatorType::PROGRESS, 'Download Micro Sfx')]
#[CommandTask('extractSfx', null, 'Extract Micro Sfx')]
#[CommandTask('buildBinaries', null, 'Build distribution binaries')]
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

    protected function checkRepoStatus(): bool
    {
        $process = Process::path(base_path());

        $prompts = [];

        $pint = join_paths(base_path(), 'vendor', 'bin', 'pint');
        if (File::exists($pint)) {
            $this->output->writeln('<comment>Checking pint.</comment>');
            $result = $process->run("{$pint} --test");
            $pattern = '/\.\s*(?P<files>\d+)\s*files.*?(?P<issues>\d+)\s*style issues.*?\r?\n?/';
            preg_match($pattern, $result->output(), $matches);
            if (isset($matches['files']) && isset($matches['issues'])) {
                $prompts['pint'] = $this->prompt('confirm',
                    label: "Pint found {$matches['issues']} style issues in {$matches['files']} files. Would you like to run pint?",
                );
                $runPint = $prompts['pint']->prompt();
                if ($runPint) {
                    $result = $process->run("{$pint}");
                    $this->setTaskMessage($result->successful() ? '<info>Pint ran successfully.</info>' : '<error>Pint failed to run.</error>');
                }
            }
        }

        $result = $process->run('git rev-parse --is-inside-work-tree');

        if (! $result->successful() || Str::firstLine($result->output()) != 'true') {
            $this->setTaskMessage('<error>Failed to check repository status.</error>');

            return false;
        }

        $commands = [];
        $table = [];
        $table[] = ['Repository Status', 'Healthy'];

        $result = $process->run('git symbolic-ref --short HEAD');

        if (! $result->successful()) {
            $this->setTaskMessage('<error>Failed to retrieve branch name.</error>');

            return false;
        }

        $branch = Str::firstLine($result->output());
        $table[] = ['Branch', $branch];

        $result = $process->run('git status --porcelain');

        if (! blank(trim($result->output()))) {
            $hint = "Branch: {$branch}";
            $prompts['commit'] = $this->prompt('confirm',
                label: 'Repository is not clean. Would you like to commit changes?',
                hint: $hint,
            );

            $commit = $prompts['commit']->prompt();
            $table[] = ['Changes will be commited', $commit ? 'Yes' : 'No'];
            $commands[] = $commit ? 'git add . --all' : null;

            if ($commit) {
                $prompts['message'] = $this->prompt('text',
                    label: 'Enter commit message:',
                    hint: $hint,
                );

                $message = $prompts['message']->prompt();
                $table[] = ['Commit Message', $message];
                $commands[] = 'git commit '.(blank($message) ? '--allow-empty-message' : "-m \"{$message}\"");

                $currentTag = app('git.version');
                $table[] = ['Current Tag', $currentTag];

                $unreleased = Str::matchesReplace(
                    config('app.version_pattern'),
                    ['major' => 0, 'minor' => 0, 'patch' => 0]
                );

                $hint = "Branch: {$branch} / Current Tag: {$currentTag}";
                $prompts['tag'] = $this->prompt('select',
                    label: 'App next version?',
                    hint: $hint,
                    default: -1,
                    options: [
                        -1 => 'Do not change',
                        1 => 'Patch',
                        2 => 'Minor',
                        3 => 'Major',
                    ],
                    transform: fn ($selected) => $selected >= 1 ? Helper::appNextVer($selected, ($currentTag == 'unreleased' ? $unreleased : $currentTag)) : $selected,
                );

                $tag = $prompts['tag']->prompt();
                $tag = $tag <= 0 ? null : $tag;
                $table[] = ['Next Tag', $tag === null ? 'No change' : $tag];
                $commands[] = $tag === null ? null : "git tag -a {$tag} -m \"{$tag}\"";

                $hint .= $tag === null ? '' : " / Next Tag: {$tag}";
                $prompts['push'] = $this->prompt('confirm',
                    label: 'Push changes to remote?',
                    hint: $hint,
                );

                $push = $prompts['push']->prompt();
                $table[] = ['Push to remote', $push ? 'Yes' : 'No'];

                if ($push) {

                    $result = $process->run('git remote show');
                    if (! $result->successful()) {
                        $this->setTaskMessage('<error>Failed to retrieve remote repositories.</error>');

                        return false;
                    }

                    $availableRemotes = Str::of($result->output())->lines(-1, PREG_SPLIT_NO_EMPTY, true);
                    if ($availableRemotes->isEmpty()) {
                        $this->setTaskMessage('<error>No remote repositories available.</error>');

                        return false;
                    }
                    $table[] = ['Available Remotes ('.$availableRemotes->count().')', Arr::join($availableRemotes->toArray(), ', ')];

                    if ($availableRemotes->count() > 1) {
                        $prompts['remotes'] = $this->prompt('multiselect',
                            label: 'Which remote or remotes would you like to push?',
                            hint: $hint,
                            options: $availableRemotes,
                            required: true,
                        );

                        $remotes = $prompts['remotes']->prompt();
                    } else {
                        $remotes = $availableRemotes->toArray();
                    }

                    $table[] = ['Push Remotes ('.count($remotes).')', Arr::join($remotes, ', ')];
                    foreach ($remotes as $remote) {
                        $commands[] = "git push {$remote} {$branch}";
                        if ($tag !== null) {
                            $commands[] = "git push {$remote} {$tag}";
                        }
                    }
                }

                foreach ($prompts as $prompt) {
                    $prompt->eraseRenderedLines();
                }

                $prompts['table'] = $this->prompt('table', headers: ['Description', 'Value'], rows: $table);
                $prompts['table']->prompt();

                $this->output->writeln('<comment>Git commands will be executed:</comment>');
                $commands = Arr::whereNotNull($commands);
                $this->output->listing($commands);

                $continue = $this->prompt('confirm',
                    label: 'Continue with git actions?',
                    hint: 'Please review the information above.',
                )->prompt();

                $prompts['table']->clear();

                if ($continue) {
                    foreach ($commands as $command) {
                        $result = $process->run($command);
                        if (! $result->successful()) {
                            $this->setTaskMessage("<error>Failed to execute command: {$command}</error>");

                            return false;
                        } else {
                            $this->setTaskMessage("<info>Command executed successfully: {$command}</info>");
                        }
                    }
                }

                if (! $continue) {
                    $this->output->writeln('<comment>Git actions cancelled.</comment>');

                    $continueBuild = $this->prompt('confirm',
                        label: 'Continue with build process?'
                    )->prompt();

                    if (! $continueBuild) {
                        $this->setTaskMessage('<error>Build process cancelled.</error>');

                        return false;
                    }
                }

            }
        }

        return true;
    }

    protected function restoreBackups(): void
    {
        $buildId = $this->config('get', 'id', 'Unknown');
        $historyPath = $this->config('get', 'backup.history');
        if (! File::exists($historyPath)) {
            return;
        }

        $history = collect(File::json($historyPath))->sortByDesc('ts')->toArray();
        $newHistory = $history;

        foreach ($history as $histKey => $backup) {
            if (File::exists($backup['src'])) {
                Log::info("Build [{$buildId}] : Backup restore skipped. Backup destination (Source) already exists.", $backup);

                continue;
            } elseif (! File::exists($backup['dest'])) {
                Log::info("Build [{$buildId}] : Backup restore skipped. Backup source (Destination) does not exist.", $backup);

                continue;
            }

            $result = $backup['isDir'] ? File::moveDirectory($backup['dest'], $backup['src']) : File::move($backup['dest'], $backup['src']);

            if ($result) {
                unset($newHistory[$histKey]);
                File::put($historyPath, json_encode(array_values($newHistory)));
                Log::info("Build [{$buildId}] : Backup restored.", $backup);
            } else {
                Log::error("Build [{$buildId}] : Backup restore failed.", $backup);
            }
        }
    }

    protected function backup(string $historyPath, string $src, string $dest, string $ts, string $buildId): bool
    {
        if (! File::exists($src)) {
            $this->setTaskMessage("<error>Source file/directory does not exist at {$src}</error>");

            return false;
        }

        if (! File::exists($historyPath)) {
            File::ensureDirectoryExists(dirname($historyPath));
            File::put($historyPath, '[]');
            $history = [];
        } else {
            $history = File::json($historyPath);
        }

        File::ensureDirectoryExists(dirname($dest));
        $result = File::isDirectory($src) ? File::moveDirectory($src, $dest) : File::move($src, $dest);

        if ($result) {
            $history[] = ['build' => $buildId, 'ts' => $ts, 'src' => $src, 'dest' => $dest, 'isDir' => File::isDirectory($src)];
            File::put($historyPath, json_encode($history, JSON_PRETTY_PRINT));
            $this->setTaskMessage("<info>Backup of {$src} to {$dest} was successful.</info>");
        } else {
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
        $config['version'] = app('git.version');
        $config['id'] = "{$config['version']}-{$config['ts.safe']}";
        $config['json.version'] = $config['version'];
        $config['json.build'] = $config['ts.safe'];
        $config['json.id'] = $config['id'];

        $config['backup.path'] = joinPaths(config('dev.build.backup.path'), "{$config['version']}-{$config['ts.safe']}");
        $config['backup.history'] = joinPaths(config('dev.build.backup.path'), 'history.json');

        foreach (config('dev.build.exclude', []) as $excludeKey => $exclude) {
            $excBase = Str::of($exclude)
                ->after(base_path())
                ->split('/'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', -1, PREG_SPLIT_NO_EMPTY)
                ->toArray();
            $config["backup.items.{$excludeKey}"] = ['src' => $exclude, 'dest' => join_paths($config['backup.path'], ...$excBase)];
        }

        //        if ($this->config('get', 'version') === 'unreleased') {
        //            $this->setTaskMessage('<error>App has not released yet.</error>');
        //
        //            return false;
        //        }

        $config['initial'] = join_paths(base_path(), "{$this->getBinary()}.phar");
        $config['path'] = join_paths(config('dev.build.path'), $config['id']);
        $config['phar'] = join_paths($config['path'], "{$config['name']}.phar");

        $config['box.binary'] = join_paths(base_path(), 'vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box'));

        $distributions = config('dev.build.micro.distributions', []);

        if ($this->option('no-distributions')) {
            $this->setTaskMessage('<comment>Skipping distributions.</comment>');
        } elseif (count($distributions) == 0) {
            $this->setTaskMessage('<comment>No distributions to build.</comment>');
        }

        if ($this->option('no-distributions') || count($distributions) == 0) {
            $this->configables['build'] = Arr::undot($config);

            return true;
        }

        $config['spc.binary'] = config('dev.build.micro.spc');
        if (blank($config['spc.binary'])) {
            $this->setTaskMessage('<error>SPC binary path is not set.</error>');

            return false;
        } elseif (! File::exists($config['spc.binary'])) {
            $this->setTaskMessage("<error>SPC binary does not exist at {$config['spc.binary']}</error>");

            return false;
        }

        File::chmod($config['spc.binary'], octdec('0755'));

        $microPath = config('dev.build.micro.path');
        $microUrl = Str::of(config('dev.build.micro.url'))->trim()->finish('/');
        $microArchivePattern = config('dev.build.micro.archivePattern', '');

        foreach ($distributions as $distribution => $micro) {
            $cnfKey = 'distributions.'.Str::replace('.', '_', $distribution);

            $micro['remote'] = Str::of($micro['remote'])->trim()->ltrim('/')->value();

            $config["{$cnfKey}.target"] = $distribution;
            $config["{$cnfKey}.output"] = join_paths($config['path'], $micro['binary']);
            $config["{$cnfKey}.os"] = $micro['os'];
            $config["{$cnfKey}.arch"] = $micro['arch'];
            $config["{$cnfKey}.md5sum"] = (Arr::has($micro, 'md5sum') && $micro['md5sum'] === true);
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

        if (! blank($backupItems)) {
            $ts = $this->config('get', 'ts.instance')->toIso8601ZuluString();
            $buildId = $this->config('get', 'id');
            $historyPath = $this->config('get', 'backup.history');

            foreach ($backupItems as $item) {
                if (! $this->backup($historyPath, $item['src'], $item['dest'], $ts, $buildId)) {
                    return false;
                }
            }
        }

        $initial = $this->config('get', 'initial');
        $boxDump = join_paths(dirname($initial), '.box_dump');

        if (File::exists($boxDump) && File::isDirectory($boxDump)) {
            File::deleteDirectory($boxDump);
            $this->setTaskMessage('<comment>Old .box_dump deleted.</comment>');
        }

        if (File::exists($initial)) {
            File::delete($initial);
            $this->setTaskMessage('<comment>Old initial .phar file deleted.</comment>');
        }

        foreach (config('dev.build.exclude', []) as $excludeKey => $exclude) {
            $this->setTaskMessage("<comment>Excluding: {$exclude}</comment>");
        }

        return true;
    }

    protected function pharAddFromString(string $path, string $file, string $content): true|string
    {
        try {
            (new \Phar($path))->addFromString($file, $content);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return true;
    }

    protected function compile(): bool
    {
        $initial = $this->config('get', 'initial');
        $phar = $this->config('get', 'phar');
        $actualOutput = $this->option('leave-initial') ? $initial : $phar;

        $buildContent = json_encode($this->config('get', 'json'), JSON_PRETTY_PRINT);
        if (File::put(base_path('build.json'), $buildContent, true) === false) {
            $this->setTaskMessage('<error>Failed to write build.json file.</error>');

            return false;
        } else {
            $this->setTaskMessage('<info>build.json file written successfully.</info>');
        }

        $boxBinary = $this->config('get', 'box.binary');
        $boxDefaults = [
            'working-dir' => base_path(),
            'config' => base_path('box.json'),
        ];

        if ($this->output->isDebug()) {
            $boxDefaults['debug'] = '';
        }

        $boxOptions = Helper::buildProcessArgs($this->option('box'), $boxDefaults);

        $process = Process::timeout($this->getTimeout())
            ->run([$boxBinary, 'compile'] + $boxOptions);

        $initFile = File::exists($initial);

        if ($process->successful()) {
            if ($initFile) {
                if (! $this->option('leave-initial')) {
                    File::ensureDirectoryExists(dirname($phar));
                    File::move($initial, $phar);
                    File::put("$phar.md5sum", File::hash($phar));
                }
                $this->setTaskMessage("<info>Compile was successful and output is at {$actualOutput}</info>");
            } else {
                $this->setTaskMessage("<comment>Compile was successful but initial output does not exist at {$initial}</comment>");
            }
        } else {
            $this->setTaskMessage("<error>Failed to compile the application. Exit Code: {$process->exitCode()}</error>");
        }

        return $process->successful() && $initFile;
    }

    protected function checkDistributions(): ?bool
    {
        $distributions = $this->config('get', 'distributions', []);
        if (count($distributions) == 0) {
            $this->setTaskMessage('<comment>No distributions to check.</comment>');

            return null;
        }

        $this->taskMessageTitle = ' | Distributions to be built:';
        foreach (array_keys($distributions) as $key => $distribution) {
            $order = $key + 1;
            $this->setTaskMessage("<info>#{$order}: {$distribution}</info>");
        }

        return true;
    }

    protected function downloadSfx(): bool
    {
        $distributions = $this->config('get', 'distributions', []);

        foreach ($distributions as $distribution) {
            $sfx = $distribution['sfx'];
            if ($sfx['localExists']) {
                $this->setTaskMessage("<comment>{$distribution['target']} Sfx already exists locally at {$sfx['local']}</comment>");

                continue;
            } elseif ($sfx['downloadExists']) {
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
                'on_headers' => function (ResponseInterface $response) {
                    $this->indicator->total((int) $response->getHeaderLine('Content-Length'));
                },
            ])->get($sfx['remote']);

            if ($response->successful()) {
                $this->setTaskMessage("<info>{$distribution['target']} Sfx downloaded successfully to {$sfx['downloadPath']}</info>");
            } else {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx download failed.</error>");
            }
        }

        return true;
    }

    protected function extractSfx(): bool
    {
        $distributions = $this->config('get', 'distributions', []);

        foreach ($distributions as $distribution) {
            $sfx = $distribution['sfx'];

            if ($sfx['localExists'] || ! $sfx['remoteArchive']) {
                continue;
            }

            if (! File::exists($sfx['downloadPath'])) {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive does not exist at {$sfx['downloadPath']}</error>");

                continue;
            }

            File::ensureDirectoryExists($sfx['extractDir']);

            $result = false;
            try {
                Archive::extractTo($sfx['downloadPath'], $sfx['extractDir']);
                $result = true;
            } catch (\Throwable $exception) {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$sfx['downloadPath']}</error>");
                $this->setTaskMessage("<error>Exception Message: {$exception->getMessage()}</error>");
            }

            if (! $result) {
                continue;
            }

            $extractedSfx = join_paths($sfx['extractDir'], $sfx['archiveFile']);

            if (! File::exists($extractedSfx)) {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$extractedSfx}</error>");
            } else {
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
        $phar = $this->config('get', $pharKey);
        $spcBinary = $this->config('get', 'spc.binary');
        $spcDefaults = [
            'debug',
            'no-interaction',
        ];

        foreach ($distributions as $distribution) {
            $sfx = $distribution['sfx'];
            $output = $distribution['output'];
            $md5sum = $distribution['md5sum'];
            $distPhar = "{$output}.phar";

            if (! $sfx['localExists']) {
                $this->setTaskMessage("<error>{$distribution['target']} Sfx does not exist at {$sfx['local']}</error>");

                continue;
            }

            File::ensureDirectoryExists(dirname($output));

            if (! File::copy($phar, $distPhar)) {
                $this->setTaskMessage("<error>Phar {$phar} could not be copied to {$distPhar} for {$distribution['target']}</error>");

                continue;
            }

            $buildContent = array_merge($this->config('get', 'json'), [
                'os' => $distribution['os'],
                'arch' => $distribution['arch'],
                'dist' => "{$distribution['os']}-{$distribution['arch']}",
            ]);
            $buildContent = json_encode($buildContent);
            $buildContentResult = $this->pharAddFromString($distPhar, 'build.json', $buildContent);
            if ($buildContentResult !== true) {
                $this->setTaskMessage("<error>Failed to add build.json to the phar file. Error: {$buildContentResult}</error>");
            }

            $spcArgs = array_merge($spcDefaults, ["with-micro={$sfx['local']}", "output={$output}"]);
            $spcCmd = Helper::buildProcessCmd([$spcBinary, 'micro:combine', $distPhar], ($sfx['spcOptions'] ?? []), $spcArgs);
            $process = Process::timeout($this->getTimeout())->run($spcCmd);

            if ($process->successful()) {
                if ($md5sum && (File::put("$output.md5sum", File::hash($output)) === false)) {
                    $this->setTaskMessage("<error>Failed to write md5sum file for {$distribution['target']}</error>");
                }
                $this->setTaskMessage("<info>{$distribution['target']} built successfully at {$output}</info>");
                if (! blank($chmod = config('dev.build.chmod')) && is_string($chmod) && is_numeric($chmod) && strlen($chmod) === 4) {
                    File::chmod($output, octdec(config('dev.build.chmod')));
                }
            } else {
                $this->setTaskMessage("<error>{$distribution['target']} build failed. Exit Code: {$process->exitCode()} - Output: {$process->errorOutput()}</error>");
                if (File::exists($output)) {
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

    private function getTimeout(): ?int
    {
        throw_if(! is_numeric($this->option('timeout')), 'The timeout value must be a number.');

        $timeout = (int) $this->option('timeout');

        return $timeout > 0 ? $timeout : null;
    }

    private function cleanUp(bool $isSignal = false): AppBuild|int
    {
        if (! $this->initalised) {
            return $isSignal ? self::SUCCESS : $this;
        }

        if (File::exists(base_path('build.json'))) {
            File::delete(base_path('build.json'));
        }

        foreach ($this->config('get', 'distributions', []) as $distribution) {
            if (File::exists($distribution['sfx']['extractDir'])) {
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
