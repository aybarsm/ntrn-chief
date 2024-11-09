<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Prompts\Progress;
use App\Services\Archive;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Psr\Http\Message\ResponseInterface;
use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters')]
#[CommandTask('selectOptions', null, 'Select Options to Distribute')]
#[CommandTask('downloadSfx', null, 'Download Micro Sfx Files')]
#[CommandTask('extractSfx', null, 'Extract Micro Sfx Files')]
class AppDistribute extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:distribute';
    protected $description = 'Command description';
    protected array $prompts = [];
    protected function setParameters(): bool
    {
        $phar = Str::of(config('app.name'))->lower()->finish('.phar')->value();
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
            ->values()
            ->toArray();

        $data['spc'] = config('dev.build.spc', []);
        $data['static'] = config('dev.build.static', []);
        $messages = [
            'spc.remote.required' => 'SPC remote configuration array is required when local file does not exist.',
            'static.*.remote.required' => 'Static remote configuration array is required when local file does not exist.',
        ];
        $rules = [
            'builds' => 'array|min:1',
            'spc' => 'array|required',
            'spc.local' => 'bail|required|string',
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
            'static.*.md5sum' => 'required|boolean',
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

        if ($validator->fails()){
            foreach($validator->errors()->all() as $error){
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
        $phar = $this->config('get', 'phar');
        $builds = $this->config('get', 'builds', []);
        $this->prompts['build'] = $this->prompt('select',
            label: 'Select Build to Distribute',
            options: $builds,
            default: 0,
            transform: function ($value) use ($phar) {
                return join_paths(config('dev.build.path'), $value, $phar);
            },
            validate: function ($value) {
                return File::exists($value) ? null : "Phar file does not exist at {$value}";
            },
        );

        $this->configables['build'] = $this->prompts['build']->prompt();
        $this->prompts['build']->hint = "Phar file: {$this->configables['build']}";

//        $this->configables['build'] = [
//            'name' => $build,
//            'path' => join_paths(config('dev.build.path'), $build),
//        ];

        $dists = $this->config('get', 'dists', []);

        $this->prompts['dist'] = $this->prompt('multiselect',
            label: 'Select Distributions to Build',
            options: $dists,
            default: $dists,
        );

        $dist = $this->prompts['dist']->prompt();

        $this->configables['dist'] = $dist;

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

        foreach ($statics as $dist => $static) {
            $progress = $this->prompt('progress', steps: 0);
            $progress = Helper::buildProgress($progress, $dist);
            $progress->config('set', 'auto.finish', true);
            $progress->config('set', 'auto.clear', true);
            $progress->config('set', 'show.finish', 2);

            $progress->start();
            $progress->advance();
            $progress->finish();
        }

        return true;
    }


    public function handle()
    {
        $this->executeTasks();
//        dump($this->config('get', 'static'));
//        dump($this->config('get', 'dist'));
    }
}
