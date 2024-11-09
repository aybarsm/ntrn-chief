<?php

declare(strict_types=1);

namespace App\Commands;

use App\Attributes\Console\CommandTask;
use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Traits\Configable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Illuminate\Filesystem\join_paths;

#[CommandTask('gatherBuilds', null, 'Gathering Builds')]
#[CommandTask('selectBuild', null, 'Select Build to Distribute')]
//#[CommandTask('setParameters', null, 'Gathering Builds')]
class AppDistribute extends TaskingCommand
{
    use Configable;

    protected $signature = 'app:distribute';
    protected $description = 'Command description';
    protected string $configablePrefix = 'dist';
    protected function gatherBuilds(): bool
    {
        $builds = collect(File::directories(config('dev.build.path')))
            ->map(function ($item) {
                return basename($item);
            })
            ->filter(function ($item) {
                return Str::isMatch('/^v(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/', $item);
            })
            ->sortDesc()
            ->values();

        if ($builds->isEmpty()) {
            $this->setTaskMessage('<error>No builds found</error>');

            return false;
        }

        $this->config('set', 'builds', $builds->toArray());

        return true;
    }

    protected function selectBuild(): bool
    {
        $build = $this->prompt('select',
            label: 'Select Build to Distribute',
            options: $this->config('get', 'builds'),
            default: 0,
        )->prompt();

        $this->configables['build'] = [
            'name' => $build,
            'path' => join_paths(config('dev.build.path'), $build),
        ];

        return true;
    }
    protected function setParameters(): bool
    {
        $distributions = config('dev.build.micro.distributions', []);

        if (blank($distributions)) {
            $this->setTaskMessage('<error>No distributions found</error>');

            return false;
        }

        $config['spc.binary'] = config('dev.build.micro.spc');
        if (blank(config('dev.build.micro.spc'))) {
            $this->setTaskMessage('<error>SPC binary path is not set.</error>');

            return false;
        } elseif (! File::exists(config('dev.build.micro.spc'))) {
            $this->setTaskMessage("<error>SPC binary does not exist at {$config['spc.binary']}</error>");

            return false;
        }

        File::chmod(config('dev.build.micro.spc'), octdec('0755'));
        $this->config('set', 'spc.binary', config('dev.build.micro.spc'));

        $microPath = config('dev.build.micro.path');
        $microUrl = Str::of(config('dev.build.micro.url'))->trim()->finish('/');
        $microArchivePattern = config('dev.build.micro.archivePattern', '');

        foreach ($distributions as $distribution => $micro) {
            $cnfKey = Str::replace('.', '_', $distribution);

            $micro['remote'] = Str::of($micro['remote'])->trim()->ltrim('/')->value();

            $config["{$cnfKey}.output"] = join_paths($config['path'], $micro['binary']);
            $config["{$cnfKey}.os"] = $micro['os'];
            $config["{$cnfKey}.arch"] = $micro['arch'];
            $config["{$cnfKey}.target"] = "{$micro['os']}-{$micro['arch']}";
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

        $this->configables['distributions'] = Arr::undot($config);
        return true;
    }

    public function handle()
    {
        $this->executeTasks();
        dump($this->configables);
    }
}
