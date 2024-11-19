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

use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters', true)]
#[CommandTask('selectOptions', null, 'Select Options to Release', true)]
#[CommandTask('uploadAssets', null, 'Upload Changed Assets')]
class AppRelease extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:release';

    protected $description = 'Release the built application';

    protected array $prompts = [];
    protected PendingRequest $client;
    protected Collection $tags;
    protected Collection $releases;
    protected array $release;
    protected Collection $assets;
    protected Collection $hashes;

    public function handle(): void
    {
        $this->executeTasks();
    }

    protected function setReleaseAndAssets(): void
    {
        $this->releases = $this->client->get('releases')->collect();
        
        $release = $this->config('get', 'info.release');
        if ($this->releases->contains('tag_name', $release['tag_name'])) {
            $this->release = $this->releases->firstWhere('tag_name', $release['tag_name']);
        }else {
            $this->release = $this->client
                ->throw(fn (Response $response) => $response->status() !== 201)
                ->post('releases', $release)
                ->json();
            $this->setTaskMessage("Release [{$this->release['id']}] created.");
        }

        if (! isset($this->release['assets'])) {
            $this->assets = $this->client->get("releases/{$this->release['id']}/assets")->collect();
        }else {
            $this->assets = collect($this->release['assets']);
        }

        $this->hashes = collect();
        $assetClient = $this->client;
        $assetClient->replaceHeaders(['Accept' => 'application/octet-stream'])
            ->throw(fn (Response $response) => $response->status() !== 200);

        foreach($this->assets as $asset){
            if (Str::endsWith($asset['name'], '.md5sum')) {
                $this->hashes->push([
                    'name' => Str::before($asset['name'], '.md5sum'),
                    'md5sum' => $assetClient->get("releases/assets/{$asset['id']}")->body(),
                ]);
            }
        }
    }

    protected function setParameters(): bool
    {
        $buildPath = config('dev.build.path', base_path('builds'));
        $data['builds'] = collect(File::directories($buildPath))
            ->map(function ($item) {
                return basename($item);
            })
            ->filter(function ($item) {
                return Str::isMatch('/^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $item);
            })
            ->sortDesc()
            ->map(function ($item) use ($buildPath) {
                return join_paths($buildPath, $item);
            })
            ->values()
            ->toArray();

        $this->configables = $data;

        $this->client = app(GitHubContract::class)->getDevClient();
        $this->tags = $this->client
            ->throw(fn (Response $response) => $response->status() !== 200)
            ->get('tags')
            ->collect();


        return true;
    }

    protected function selectOptions(): bool
    {
        $builds = $this->config('get', 'builds', []);
        $info = ['build' => []];
        $this->prompts['build'] = $this->prompt('select',
            label: 'Select Build to Distribute',
            options: $builds,
            default: 0,
            validate: function ($value) use (&$info) {
                $info['phar'] = join_paths($value, config('dev.build.phar'));
                $info['pharMd5'] = "{$info['phar']}.md5sum";
                $info['build']['file'] = join_paths($value, 'build.json');

                foreach(['phar', 'pharMd5', 'build.file'] as $path){
                    if (! File::exists($filePath = data_get($info, $path))) {
                        return "Required file not found at {$filePath}";
                    }
                }

                return null;
            },
        );

        $buildPath = $this->prompts['build']->prompt();
        $this->configables['build'] = $buildPath;
        $info['build']['data'] = File::json($info['build']['file']);
        throw_if(
            $this->tags->where('name', $info['build']['data']['version'])->isEmpty(),
            "Tag [{$info['build']['data']['version']}] not found in repository"
        );
        $info['release'] = [
            'tag_name' => $info['build']['data']['version'],
            'target_commitish' => app('git.branch'),
            'name' => $info['build']['data']['version'],
            'body' =>$info['build']['data']['id'],
            'draft' => true,
            'prerelease' => false,
            'generate_release_notes' => false,
        ];
        $this->configables['info'] = $info;

        $this->setReleaseAndAssets();

        $files = Arr::map(File::files($this->configables['build']), fn ($file) => $file->getFilename());

        $statics = config('dev.build.static', []);
        $defaults = collect();
        Arr::map(array_merge([['binary' => basename($info['phar'])]], $statics), callback: function ($item) use (&$defaults, $buildPath) {
            $label = Arr::has($item, ['os', 'arch']) ? "{$item['os']}_{$item['arch']}" : '';
            $defaults->push([
                'name' => $item['binary'],
                'label' => $label,
                'path' => join_paths($buildPath, $item['binary']),
            ]);
            $defaults->push([
                'name' => "{$item['binary']}.md5sum",
                'label' => blank($label) ? '' : "md5sum:{$label}",
                'path' => join_paths($buildPath, $item['binary']),
            ]);
        });

        $this->prompts['files'] = $this->prompt('multiselect',
            label: 'Select Assets to Release',
            options: $files,
            required: true,
            scroll: count($files),
            default: $defaults->pluck('name')->toArray(),
            transform: fn ($items) => Arr::map($items, fn ($item) => $defaults->firstWhere('name', $item) ?? ['name' => $item]),
        );

        $this->configables['files'] = collect($this->prompts['files']->prompt())
            ->sortBy('name')
            ->sortBy(fn ($item) => Str::endsWith($item['name'], '.md5sum') ? 1 : 0)
            ->toArray();

        return true;
    }

    protected function uploadAssets(): bool
    {
        $files = collect($this->config('get', 'files', []))
            ->sortBy('name')
            ->sortBy(fn ($item) => Str::endsWith($item['name'], '.md5sum') ? 1 : 0)
            ->toArray();

        foreach($files as $file){
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
        }



        $response = Http::sink($sfx['remote']['saveAs'])->withOptions([
            'progress' => function ($dlSize, $dlCompleted) use ($progress) {
                $progress->progress($dlCompleted);
            },
            'on_headers' => function (ResponseInterface $response) use ($progress) {
                $progress->total((int) $response->getHeaderLine('Content-Length'));
            },
        ])->get($sfx['remote']['url']);

        dump($files);



//        dump($this->assets);
//        dump($this->releases);
//        dump($this->release);
//        dump($releases->json());
        exit();
//        $assets = $this->config('get', 'assets');
//
//        foreach($assets as $asset){
//            if ($this->assets->firstWhere('name', $asset['name'])->isEmpty()) {
//                $this->client->post("releases/{$this->release['id']}/assets", [
//                    'name' => $asset['name'],
//                    'label' => $asset['label'],
//                    'data' => File::get($asset['path']),
//                ]);
//            }
//        }
        return true;
    }
}
