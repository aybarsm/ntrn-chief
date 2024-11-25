<?php

declare(strict_types=1);

namespace App\Commands\Filesystem;

use App\Framework\Commands\Command;
use App\Services\Helper;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use function Illuminate\Filesystem\join_paths;

class StandardNames extends Command
{
    protected $signature = 'fs:standard-names
    {cwd=. : The directory to start from}
    {only=all : The type of items to standardise (file|files|directory|directories|dir|dirs|folder|folders)}
    {--nh|no-hidden : Do not include hidden files and directories}
    {--nr|non-recursive : Do not search recursively}
    {--d|dry-run : Do not perform any actions}';

    protected $description = 'Standardise file and directory names';
    protected string $expected;
    protected array $search;
    protected array $replace;
    protected string $remainder;
    protected bool $ascii;
    protected string $cwd;
    protected string $for;
    protected bool $noHidden;
    protected bool $nonRecursive;
    protected bool $dryRun;
    protected bool $useFiles;
    protected bool $useDirs;
    protected array $items;

    public function handle(
        #[Config('filesystems.standard.names.expected')] string $expected,
        #[Config('filesystems.standard.names.search')] array $search,
        #[Config('filesystems.standard.names.replace')] array $replace,
        #[Config('filesystems.standard.names.remainder')] string $remainder,
        #[Config('filesystems.standard.names.ascii')] string|int|bool $ascii,
    ): void
    {
        $this->cwd = Helper::getCwd($this->argument('cwd'));
        $this->expected = Helper::pattern($expected);
        $this->search = $search;
        $this->replace = $replace;
        $this->remainder = $remainder;
        $this->ascii = truthy($ascii);
        $this->noHidden = $this->option('no-hidden');
        $this->nonRecursive = $this->option('non-recursive');
        $this->dryRun = $this->option('dry-run');

        if (blank($this->expected)) {
            $this->error('Invalid expected pattern');

            return;
        }

        if (blank($this->search) || blank($this->replace) || count($this->search) !== count($this->replace)) {
            $this->error('Search and replace arrays must be non-empty and of equal length');

            return;
        }

        if (blank($this->remainder)) {
            $this->error('Invalid remainder value');

            return;
        }

        if (! File::isDirectory($this->cwd)) {
            $this->error("The directory [{$this->cwd}] does not exist");

            return;
        }

        $this->items = with(File::in($this->cwd)
            ->ignoreDotFiles($this->noHidden)
            ->depth($this->nonRecursive ? '== 0' : '>= 0')
            ->notName($this->expected)
            ->sortByName()
            ->when(
                in_array($this->argument('only'), ['file', 'files']),
                fn ($finder) => $finder->files()
            )
            ->when(
                in_array($this->argument('only'), ['directory', 'directories', 'dir', 'dirs', 'folder', 'folders']),
                fn ($finder) => $finder->directories()
            ),
            fn (Finder $finder) => array_values(iterator_to_array($finder))
        );

        $this->title("Standardising names in [{$this->cwd}]");

        $this->startProcess();
    }

    protected function getEntries(bool $isDirs): array
    {
        return File::notMatching(
            path: $this->cwd,
            pattern: $this->expected,
            dirs: $isDirs,
            recursive: $this->recursive,
            hidden: $this->useHidden,
            basename: true,
            sort: null,
            returnFullPath: true,
            values: true,
            asArray: true,
        );
    }

    protected function startProcess(): void
    {
        $this->entries->each(function (string $path, int $key) {
            $dir = dirname($path);
            $name = basename($path);

            $newName = Str::of($name)
                ->when($this->ascii, fn ($str) => $str->ascii())
                ->replaceMatches($this->search, $this->replace)
                ->replaceMatches('/./', function ($matches) {
                    return Str::match($this->expected, $matches[0]) ? $matches[0] : $this->remainder;
                })->value();

            $this->newLine();
            $this->comment("Path: {$path}");
            $this->comment("[{$name}] will be renamed to [{$newName}]");

            if (! $this->dryRun) {
                $res = File::move($path, join_paths($dir, $newName));
                if ($res) {
                    $this->info("Renamed [{$name}] to [{$newName}]");
                } else {
                    $this->error("Failed to rename [{$name}] to [{$newName}]");
                }
            }
        });
    }
}
