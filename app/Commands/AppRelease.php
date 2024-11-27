<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Framework\Commands\TaskingCommand;
use App\Services\GitHub\Contracts\GitHubContract;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Ramsey\Collection\Sort;

use function Illuminate\Filesystem\join_paths;

#[CommandTask('setParameters', null, 'Set Parameters', true)]
//#[CommandTask('selectOptions', null, 'Select Options to Release', true)]
//#[CommandTask('uploadAssets', null, 'Upload Changed Assets')]
class AppRelease extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:release';

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

                foreach ([$data['phar']['path'], $data['phar']['md5'], $data['build']['file']] as $path) {
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

        if ($this->tags->where('name', $data['build']['data']['version'])->isEmpty()) {
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
        $data['files'] = collect(Arr::mapWithKeys(File::files($data['build']['path']), function ($file, $key) use ($data, $statics) {
            $isMd5 = $file->getExtension() === 'md5sum';
            $static = $statics->firstWhere('binary', $file->getFilenameWithoutExtension());
            $assetId = $this->assets->firstWhere('name', $file->getFilename())?->get('id');
            $uploaded = $assetId !== null;
            $isDefault = $static !== null || in_array($file->getPathname(), [$data['phar']['path'], $data['phar']['md5']]);
            $label = match (true) {
                $isMd5 => "md5sum:{$file->getFilenameWithoutExtension()}",
                $static !== null => "dist:{$static['os']}_{$static['arch']}",
                default => ''
            };

            return [
                "file_{$key}" => [
                    'name' => $file->getFilename(),
                    'label' => $label,
                    'prompt' => $file->getFilename().($uploaded ? ' (Exists - Overwritten if selected)' : ''),
                    'path' => $file->getPathname(),
                    'default' => $isDefault,
                    'uploaded' => $uploaded,
                    'selected' => $isDefault && ! $uploaded,
                    'asset_id' => $assetId,
                ],
            ];
        }))
            ->sortBy('name')
            ->sortBy(fn ($file) => $file['default'] ? 0 : 1);

        dump($data['files']);
        exit();

        $this->prompts['files'] = $this->prompt('multiselect',
            label: 'Select Assets to Release',
            options: $data['files']->mapWithKeys(fn ($file, $key) => [$key => $file['prompt']])->toArray(),
            required: true,
            scroll: $data['files']->count(),
            default: $data['files']->filter(fn ($file) => $file['selected'])->keys()->toArray(),
        );

        $selected = $this->prompts['files']->prompt();
        $data['uploads'] = $data['files']
            ->filter(fn ($file, $key) => in_array($key, $selected))->values()
            ->toArray();

        $this->configables = $data;

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
        } else {
            return $this->client
                ->get("releases/{$this->release['id']}/assets")
                ->collect();
        }
    }

    protected function uploadAssets(): bool
    {
        $files = $this->config('get', 'uploads');
        //        $uploader = $this->client->attach()

        foreach ($files as $file) {
            if ($file['uploaded']) {
                $response = $this->client->delete("releases/assets/{$file['name']}");
            }
            //            $progress = $this->prompt('progress', steps: 0);
            //            $progress = Helper::uploadProgress($progress, $sfx['remote']['url'], "for {$sfx['dist']}");
            //            $progress->config('set', 'auto.finish', true);
            //            $progress->config('set', 'auto.clear', true);
            //            $progress->config('set', 'show.finish', 2);
            //            $response = $this->client->withOptions([
            //                'progress' => function ($dlSize, $dlCompleted) use ($progress) {
            //                    $progress->progress($dlCompleted);
            //                },
            //                'on_headers' => function (ResponseInterface $response) use ($progress) {
            //                    $progress->total((int) $response->getHeaderLine('Content-Length'));
            //                },
            //            ])->get($sfx['remote']['url']);
        }

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
