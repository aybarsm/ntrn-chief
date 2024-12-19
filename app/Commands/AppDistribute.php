<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Framework\Commands\TaskingCommand;
use App\Services\Archive;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters', true)]
#[CommandTask('selectOptions', null, 'Select Options to Distribute')]
#[CommandTask('downloadSfx', null, 'Download Micro Sfx Files')]
#[CommandTask('extractSfx', null, 'Extract Micro Sfx Files')]
#[CommandTask('buildStatics', null, 'Build Static Binaries')]
class AppDistribute extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:distribute
    {--latest : Distribute the latest build}
    {--dist=* : Distributions to build}';

    protected $description = 'Distribute the built application';

    protected array $prompts = [];

    public function handle()
    {
        $this->executeTasks();

    }

    protected function setParameters(): bool
    {
        $pharName = Str::of(config('app.name'))->lower()->finish('.phar')->value();
        $buildPath = config('dev.build.path', base_path('builds'));
        $data = [];
        $data['builds'] = collect(File::directories($buildPath))
            ->map(function ($item) {
                return basename($item);
            })
            ->filter(function ($item) {
                return Str::isMatch('/^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $item);
            })
            ->sortDesc()
            ->map(function ($item) use ($buildPath, $pharName) {
                return join_paths($buildPath, $item, $pharName);
            })
            ->filter(function ($item) {
                return File::exists($item);
            })
            ->values()
            ->toArray();

        $data['spc'] = config('dev.build.spc', []);
        $data['static'] = config('dev.build.static', []);
        $messages = [
            'builds.min' => 'No matching builds found in the builds directory.',
            'spc.remote.required' => 'SPC remote configuration array is required when local file does not exist.',
            'static.*.remote.required' => 'Static remote configuration array is required when local file does not exist.',
        ];
        $rules = [
            'builds' => 'array|min:1',
            'spc' => 'array|required',
            'spc.local' => 'bail|required|string|file_exists',
            'spc.args' => 'array',
            'spc.chmod' => 'required|string|numeric',
            'spc.remote' => 'array',
            'spc.remote.url' => 'string|url',
            'spc.remote.saveAs' => 'string',
            'spc.remote.archive' => 'boolean',
            'spc.remote.archiveFile' => 'string|required_if_accepted:spc.remote.archive',
            'static' => 'array|min:1',
            'static.*.binary' => 'required|string',
            'static.*.os' => 'required|string|in:darwin,linux,windows|distinct_with:static.*.arch',
            'static.*.arch' => 'required|string|in:x86_64,aarch64',
            'static.*.local' => 'required|string',
            'static.*.args' => 'array',
            'static.*.chmod' => 'string|numeric',
            'static.*.remote' => 'bail|array',
            'static.*.remote.url' => 'string|url',
            'static.*.remote.saveAs' => 'string',
            'static.*.remote.archive' => 'boolean',
            'static.*.remote.archiveFile' => 'string|required_if_accepted:static.*.remote.archive',
            'static.*.dist' => 'prohibited',
            'static.*.localExists' => 'prohibited',
            'static.*.downloadExists' => 'prohibited',
        ];

        $validator = Validator::make($data, $rules, $messages);

        $validator->sometimes('spc.remote', 'required|required_array_keys:url,saveAs,archive', function (Fluent $input, Fluent $item) {
            return ! File::exists((string) $item->get('local'));
        });
        $validator->sometimes('static.*.remote', 'required|required_array_keys:url,saveAs,archive', function (Fluent $input, Fluent $item) {
            return ! File::exists((string) $item->get('local'));
        });

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->setTaskMessage("<error>{$error}</error>");
            }

            return false;
        }

        $data['static'] = Arr::mapWithKeys($data['static'], function ($item, $key) {
            $item['localExists'] = File::exists($item['local']);
            $item['downloadExists'] = ! $item['localExists'] && File::exists($item['remote']['saveAs']);
            $item['dist'] = "{$item['os']}-{$item['arch']}";

            return [$item['dist'] => $item];
        });

        $data['dists'] = array_keys($data['static']);

        $this->configables = $data;

        return true;
    }

    protected function selectOptions(): bool
    {
        $builds = $this->config('get', 'builds', []);
        $info = [
            'build' => [
                'file' => null,
                'data' => null,
            ],
        ];

        if ($this->option('latest')){
            $this->configables['phar'] = $builds[0];
            $info['build']['file'] = join_paths(dirname($builds[0]), 'build.json');
            if (! File::exists($info['build']['file'])) {
                $this->setTaskMessage("<error>Build information file does not exist at {$info['build']['file']} for latest.</error>");

                return false;
            }
        }else {
            $this->prompts['phar'] = $this->prompt('select',
                label: 'Select Build to Distribute',
                options: $builds,
                default: 0,
                validate: function ($value) use (&$info) {
                    $info['build']['file'] = join_paths(dirname($value), 'build.json');
                    if (! File::exists($info['build']['file'])) {
                        return "Build information file does not exist at {$info['build']['file']}";
                    }

                    return null;
                },
            );

            $this->configables['phar'] = $this->prompts['phar']->prompt();
        }

        $info['build']['data'] = File::json($info['build']['file']);
        $this->configables['info'] = $info;

        $dists = $this->config('get', 'dists', []);

        if (! blank($this->option('dist'))) {
            if (count(array_intersect($this->option('dist'), $dists)) !== count($this->option('dist'))) {
                $distList = Arr::join($this->option('dist'), ', ');
                $distAvail = Arr::join($dists, ', ');
                $this->setTaskMessage("<error>Invalid distribution(s) [{$distList}] selected. Available: {$distAvail}</error>");

                return false;
            }
            $this->configables['dist'] = $this->option('dist');
        }else {
            $this->prompts['dist'] = $this->prompt('multiselect',
                label: 'Select Distributions to Build',
                options: $dists,
                default: $dists,
            );

            $dist = $this->prompts['dist']->prompt();

            $this->configables['dist'] = $dist;
        }

        return true;
    }

    protected function downloadSfx(): bool
    {
        $statics = Arr::only($this->config('get', 'static'), $this->config('get', 'dist'));

        foreach ($statics as $sfx) {
            if ($sfx['localExists']) {
                $this->setTaskMessage("<comment>{$sfx['dist']} Sfx already exists locally at {$sfx['local']}</comment>");

                continue;
            } elseif ($sfx['downloadExists']) {
                $this->setTaskMessage("<comment>{$sfx['dist']} Sfx already downloaded at {$sfx['remote']['saveAs']}</comment>");

                continue;
            }

            File::ensureDirectoryExists(dirname($sfx['remote']['saveAs']));
            $progress = $this->prompt('progress', steps: 0);
            $progress = Helper::downloadProgress($progress, $sfx['remote']['url'], "for {$sfx['dist']}");
            $progress->config('set', 'auto.finish', true);
            $progress->config('set', 'auto.clear', true);
            $progress->config('set', 'show.finish', 2);

            $response = Http::sink($sfx['remote']['saveAs'])->withOptions([
                'progress' => function ($dlSize, $dlCompleted) use ($progress) {
                    $progress->progress($dlCompleted);
                },
                'on_headers' => function (ResponseInterface $response) use ($progress) {
                    $progress->total((int) $response->getHeaderLine('Content-Length'));
                },
            ])->get($sfx['remote']['url']);

            if ($response->successful()) {
                $this->setTaskMessage("<info>{$sfx['dist']} Sfx downloaded successfully to {$sfx['remote']['saveAs']}</info>");
                $this->config('set', "static.{$sfx['dist']}.downloadExists", true);
            } else {
                $this->setTaskMessage("<error>{$sfx['dist']} Sfx download failed.</error>");
            }
        }

        return true;
    }

    protected function extractSfx(): bool
    {
        $statics = Arr::only($this->config('get', 'static'), $this->config('get', 'dist'));
        foreach ($statics as $sfx) {
            if ($sfx['localExists'] || ! $sfx['remote']['archive']) {
                continue;
            }

            if (! File::exists($sfx['remote']['saveAs'])) {
                $this->setTaskMessage("<error>{$sfx['dist']} Sfx archive does not exist at {$sfx['remote']['saveAs']}</error>");

                continue;
            }

            $extractDir = dirname($sfx['local']);
            $extracted = join_paths($extractDir, $sfx['remote']['archiveFile']);

            if (File::exists($extracted)) {
                $this->setTaskMessage("<error>{$sfx['dist']} There is already an extracted archive file at {$extracted}. Please delete it before proceeding.</error>");

                continue;
            }

            File::ensureDirectoryExists($extractDir);

            try {
                Archive::extractTo($sfx['remote']['saveAs'], $extractDir);
            } catch (\Throwable $exception) {
                $this->setTaskMessage("<error>{$sfx['dist']} Sfx archive could not be extracted from {$sfx['remote']['saveAs']}</error>");
                $this->setTaskMessage("<error>Exception Message: {$exception->getMessage()}</error>");

                continue;
            }

            File::move($extracted, $sfx['local']);
            $this->config('set', "static.{$sfx['dist']}.localExists", true);
            $this->setTaskMessage("<info>{$sfx['dist']} Sfx archive extracted successfully to {$sfx['local']}</info>");
        }

        return true;
    }

    protected function buildStatics(): bool
    {
        $statics = Arr::only($this->config('get', 'static'), $this->config('get', 'dist'));
        $basePhar = $this->config('get', 'phar');
        $baseBuildInfo = $this->config('get', 'info.build.data');

        $spcBinary = $this->config('get', 'spc.local');
        $spcChmod = $this->config('get', 'spc.chmod');
        File::chmod($spcBinary, octdec($spcChmod));
        $spcDefaults = $this->config('get', 'spc.args', []);

        foreach ($statics as $dist => $sfx) {
            if (! $sfx['localExists']) {
                $this->setTaskMessage("<error>{$sfx['dist']} Sfx does not exist at {$sfx['local']}</error>");

                continue;
            }

            $static = join_paths(dirname($basePhar), $sfx['binary']);
            $staticMd5sum = "{$static}.md5sum";
            $distPhar = "{$static}.phar";
            foreach ([$static, $staticMd5sum, $distPhar] as $file) {
                if (File::exists($file)) {
                    $this->setTaskMessage("<info>Deleting existing file for {$sfx['dist']} at {$file}</info>");
                    File::delete($file);
                }
            }
            $buildInfo = json_encode(array_merge($baseBuildInfo, [
                'os' => $sfx['os'],
                'arch' => $sfx['arch'],
                'dist' => $sfx['dist'],
            ]));

            File::ensureDirectoryExists(dirname($static));
            if (! File::copy($basePhar, $distPhar)) {
                $this->setTaskMessage("<error>Phar {$basePhar} could not be copied to {$distPhar} for {$sfx['dist']}</error>");

                continue;
            }

            $buildInfoResult = $this->pharAddFromString($distPhar, 'build.json', $buildInfo);
            if ($buildInfoResult !== true) {
                $this->setTaskMessage("<error>Failed to add build.json to the phar file. Error: {$buildInfoResult}</error>");

                continue;
            }

            $spcCmd = Helper::buildProcessCmd([$spcBinary, 'micro:combine', $distPhar], [
                'with-micro' => $sfx['local'],
                'output' => $static,
            ], $spcDefaults);

            $building = Process::timeout(60)->start($spcCmd);

            $process = $building->wait();

            if ($process->successful()) {
                if (File::exists($distPhar)) {
                    File::delete($distPhar);
                }

                if (isset($sfx['chmod'])) {
                    File::chmod($static, octdec($sfx['chmod']));
                    $this->setTaskMessage("<info>Chmod set to {$sfx['chmod']} for {$sfx['dist']} at {$static}</info>");
                }

                File::put($staticMd5sum, File::hash($static));
                $this->setTaskMessage("<info>MD5Sum created for {$sfx['dist']} is at {$staticMd5sum}</info>");

                if (isset($sfx['sanityCheck'])) {
                    $sanityCmd = Str::replace(['{{BINARY}}', '{{BASE_PATH}}'], [$static, base_path()], $sfx['sanityCheck']);
                    $sanity = Process::run($sanityCmd);
                    if ($sanity->successful()) {
                        $this->setTaskMessage("<info>Sanity Check for {$sfx['dist']} passed successfully</info>");
                    } else {
                        $this->setTaskMessage("<error>Sanity Check for {$sfx['dist']} failed. Exit Code: {$sanity->exitCode()}</error>");
                    }
                }

                $this->setTaskMessage("<info>Static Binary for {$sfx['dist']} created successfully at {$static}</info>");
            } else {
                $this->setTaskMessage("<error>Static Binary for {$sfx['dist']} could not be created. Exit Code: {$process->exitCode()}</error>");
            }
        }
        exit();

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
}
