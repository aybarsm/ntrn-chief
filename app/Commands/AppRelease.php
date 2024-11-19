<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Framework\Commands\TaskingCommand;
use App\Services\GitHub\Contracts\GitHubContract;
use App\Traits\Configable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use Ramsey\Collection\Sort;
use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters', true)]
//#[CommandTask('selectOptions', null, 'Select Options to Release', true)]
//#[CommandTask('uploadAssets', null, 'Upload Changed Assets')]
class AppRelease extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:release
    {--force: Force (overwrite assets) release}';

    protected $description = 'Release the built application';

    protected array $prompts = [];
    protected PendingRequest $client;
    protected Collection $tags;
    protected array $release;
    protected Collection $assets;

    public function handle(): void
    {
        $this->executeTasks();
    }

    protected function setParameters(): bool
    {
        $data['build']['path'] = config('dev.build.path', base_path('builds'));
        $data['builds'] = File::matching(
            path: $data['build']['path'],
            pattern: config('dev.build.id_pattern'),
            dirs: true,
            sort: Sort::Descending,
            returnFullPath: false,
        );

        $this->client = app(GitHubContract::class)->getDevClient();

        $this->prompts['build'] = $this->prompt('select',
            label: 'Select Build to Release',
            options: $data['builds'],
            default: 0,
            validate: function ($value) use (&$data) {
                $data['phar']['path'] = join_paths($value, config('dev.build.phar'));
                $data['phar']['md5'] = "{$data['phar']['path']}.md5sum";
                $data['build']['file'] = join_paths($value, 'build.json');

                foreach([$data['phar']['path'], $data['phar']['md5'], $data['build']['file']] as $path){
                    if (! File::exists($path)) {
                        return "Required file not found at {$path}";
                    }
                }

                return null;
            },
            transform: function ($value) use ($data) {
                return joinPaths($data['build']['path'], $value);
            }
        );

        $data['build']['path'] = $this->prompts['build']->prompt();
        $data['build']['data'] = File::json($data['build']['file']);

        $this->tags = $this->getTags();

        if ($this->tags->where('name', $data['build']['data']['version'])->isEmpty()){
            $this->setTaskMessage("Tag [{$data['build']['data']['version']}] not found in repository.");
            return false;
        }

        $data['release'] = [
            'tag_name' => $data['build']['data']['version'],
            'target_commitish' => app('git.branch'),
            'name' => $data['build']['data']['version'],
            'body' => $data['build']['data']['id'],
            'draft' => true,
            'prerelease' => false,
            'generate_release_notes' => false,
        ];

        $this->release = $this->getRelease($data['release']);
        $this->assets = $this->getAssets();

        $statics = collect(config('dev.build.static', []));
        $files = collect(Arr::mapWithKeys(File::files($data['build']['path']), function ($file, $key) use ($data, $statics) {
            $isMd5 = $file->getExtension() === 'md5sum';
            $static = $statics->firstWhere('binary', $file->getFilenameWithoutExtension());
            $uploaded = $this->assets->contains('name', $file->getFilename());
            $isDefault = $static !== null || in_array($file->getPathname(), [$data['phar']['path'], $data['phar']['md5']]);
            $label = match(true) {
                $isMd5 => "md5sum:{$file->getFilenameWithoutExtension()}",
                $static !== null => "dist:{$static['os']}_{$static['arch']}",
                default => ''
            };
            return [
                "file_{$key}" => [
                    'name' => $file->getFilename(),
                    'label' => $label,
                    'prompt' => $file->getFilename() . ($uploaded ? ' (Exists - Overwritten if selected)' : ''),
                    'path' => $file->getPathname(),
                    'default' => $isDefault,
                    'uploaded' => $uploaded,
                    'selected' => $isDefault && ! $uploaded,
                ]
            ];
        }))
            ->sortBy('name')
            ->sortBy(fn ($file) => $file['default'] ? 0 : 1);

        $this->prompts['files'] = $this->prompt('multiselect',
            label: 'Select Assets to Release',
            options: $files->mapWithKeys(fn ($file, $key) => [$key => $file['prompt']])->toArray(),
            required: true,
            scroll: count($files),
            default: $files->filter(fn ($file) => $file['selected'])->keys()->toArray(),
        );

        $uploads = $this->prompts['files']->prompt();
        $data['files'] = $files->filter(fn ($file, $key) => in_array($key, $uploads))->values()->toArray();

        $this->configables = $data;
        dump($this->configables);
        exit();

        return true;
    }

    protected function getTags(): Collection
    {
        return $this->client
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->get('tags')
            ->collect();
    }

    protected function getRelease(array $default): array
    {
        $release = $this->client
            ->get('releases')
            ->collect()
            ->firstWhere('tag_name', $default['tag_name']);

        if ($release === null) {
            $release = $this->client
                ->throw(fn (Response $response) => $response->status() !== 201)
                ->post('releases', $default)
                ->json();
            $this->setTaskMessage("Release [{$this->release['id']}] created.");
        }

        return $release;
    }

    protected function getAssets(): Collection
    {
        if (Arr::has($this->release, 'assets') and ! blank($this->release['assets'])) {
            return collect($this->release['assets']);
        }else {
            return $this->client
                ->get("releases/{$this->release['id']}/assets")
                ->collect();
        }
    }

//    protected function setRelease(): void
//    {
//
//    }

//    protected function setReleaseAndAssets(): void
//    {
//        $this->releases = $this->client->get('releases')->collect();
//
//        $release = $this->config('get', 'info.release');
//        if ($this->releases->contains('tag_name', $release['tag_name'])) {
//            $this->release = $this->releases->firstWhere('tag_name', $release['tag_name']);
//        }else {
//            $this->release = $this->client
//                ->throw(fn (Response $response) => $response->status() !== 201)
//                ->post('releases', $release)
//                ->json();
//            $this->setTaskMessage("Release [{$this->release['id']}] created.");
//        }
//
//        if (! isset($this->release['assets'])) {
//            $this->assets = $this->client->get("releases/{$this->release['id']}/assets")->collect();
//        }else {
//            $this->assets = collect($this->release['assets']);
//        }
//
//        $this->hashes = collect();
//        $assetClient = $this->client;
//        $assetClient->replaceHeaders(['Accept' => 'application/octet-stream'])
//            ->throw(fn (Response $response) => $response->status() !== 200);
//
//        foreach($this->assets as $asset){
//            if (Str::endsWith($asset['name'], '.md5sum')) {
//                $this->hashes->push([
//                    'name' => Str::before($asset['name'], '.md5sum'),
//                    'md5sum' => $assetClient->get("releases/assets/{$asset['id']}")->body(),
//                ]);
//            }
//        }
//    }

//    protected function selectOptions(): bool
//    {
//        $builds = $this->config('get', 'builds', []);
//
//        [$build, $phar] = [[], []];
//
//
//
//        $build['path'] = $this->prompts['build']->prompt();
//        $build['data'] = File::json($build['path']);
//
//        throw_if(
//            $this->tags->where('name', $info['build']['data']['version'])->isEmpty(),
//            "Tag [{$info['build']['data']['version']}] not found in repository"
//        );
//
//        $info['release'] = [
//            'tag_name' => $info['build']['data']['version'],
//            'target_commitish' => app('git.branch'),
//            'name' => $info['build']['data']['version'],
//            'body' =>$info['build']['data']['id'],
//            'draft' => true,
//            'prerelease' => false,
//            'generate_release_notes' => false,
//        ];
//        $this->configables['info'] = $info;
//
//        $this->setReleaseAndAssets();
//
//        $statics = collect(config('dev.build.static', []));
//        $files = Arr::mapWithKeys(File::files($this->configables['build']), function ($file) use ($buildPath, $statics) {
//
//        });
//            ->map(function ($item) {
//                return $item->getFilename();
//            })->filter(function ($item) {
//                return ! Str::endsWith($item, '.md5sum');
//            })->values()->toArray();
//
//        $defaults = collect();
//        Arr::map(array_merge([['binary' => basename($info['phar'])]], $statics), callback: function ($item) use (&$defaults, $buildPath) {
//            $label = Arr::has($item, ['os', 'arch']) ? "{$item['os']}_{$item['arch']}" : '';
//            $defaults->push([
//                'name' => $item['binary'],
//                'label' => $label,
//                'path' => join_paths($buildPath, $item['binary']),
//            ]);
//            $defaults->push([
//                'name' => "{$item['binary']}.md5sum",
//                'label' => blank($label) ? '' : "md5sum:{$label}",
//                'path' => join_paths($buildPath, $item['binary']),
//            ]);
//        });
//
//
//
//        $this->prompts['files'] = $this->prompt('multiselect',
//            label: 'Select Assets to Release',
//            options: $files,
//            required: true,
//            scroll: count($files),
//            default: $defaults->pluck('name')->toArray(),
//            transform: function ($values){
//                dump($values);
//                return $values;
//            }
////            transform: fn ($items) => Arr::map($items, fn ($item) => $defaults->firstWhere('name', $item) ?? ['name' => $item]),
//        );
//
//        $this->configables['files'] = $this->prompts['files']->prompt();
//
////        dump($this->prompts['files']->value());
//        exit();
//
//
//
//
//        return true;
//    }
//
//    protected function uploadAssets(): bool
//    {
//        $files = collect($this->config('get', 'files', []))
//            ->sortBy('name')
//            ->sortBy(fn ($item) => Str::endsWith($item['name'], '.md5sum') ? 1 : 0)
//            ->toArray();
//
//        foreach($files as $file){
//            $progress = $this->prompt('progress', steps: 0);
//            $progress = Helper::uploadProgress($progress, $sfx['remote']['url'], "for {$sfx['dist']}");
//            $progress->config('set', 'auto.finish', true);
//            $progress->config('set', 'auto.clear', true);
//            $progress->config('set', 'show.finish', 2);
//
//            $asset = $this->assets->firstWhere('name', $file['name']);
//            if (! $asset) {
//
//            }
//            else {
//                $hash = $this->hashes->firstWhere('name', $file['name']);
//                if ($hash && $hash['md5sum'] !== md5_file($file['path'])) {
//                    dump("Uploading {$file['name']}");
//                }
//            }
//        }
//
//
//
//        $response = Http::sink($sfx['remote']['saveAs'])->withOptions([
//            'progress' => function ($dlSize, $dlCompleted) use ($progress) {
//                $progress->progress($dlCompleted);
//            },
//            'on_headers' => function (ResponseInterface $response) use ($progress) {
//                $progress->total((int) $response->getHeaderLine('Content-Length'));
//            },
//        ])->get($sfx['remote']['url']);
//
//        dump($files);
//
//
//
////        dump($this->assets);
////        dump($this->releases);
////        dump($this->release);
////        dump($releases->json());
//        exit();
////        $assets = $this->config('get', 'assets');
////
////        foreach($assets as $asset){
////            if ($this->assets->firstWhere('name', $asset['name'])->isEmpty()) {
////                $this->client->post("releases/{$this->release['id']}/assets", [
////                    'name' => $asset['name'],
////                    'label' => $asset['label'],
////                    'data' => File::get($asset['path']),
////                ]);
////            }
////        }
//        return true;
//    }
}
