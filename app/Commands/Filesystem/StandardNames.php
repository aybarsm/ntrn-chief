<?php

declare(strict_types=1);

namespace App\Commands\Filesystem;

use App\Framework\Commands\Command;
use App\Services\Helper;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ramsey\Collection\Sort;
use Symfony\Component\Finder\Finder;

use function Illuminate\Filesystem\join_paths;

class StandardNames extends Command
{
    protected $signature = 'fs:standard-names
    {cwd=. : The directory to start from}
    {only=all : The type of items to standardise (file|files|directory|directories|dir|dirs|folder|folders)}
    {--ns|no-stop : Do not stop on error}
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

    protected bool $noStop;

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
    ): void {
        $this->cwd = Helper::getCwd($this->argument('cwd'));
        $this->expected = Helper::pattern($expected);
        $this->search = $search;
        $this->replace = $replace;
        $this->remainder = $remainder;
        $this->ascii = truthy($ascii);
        $this->noStop = $this->option('no-stop');
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
            ->sortByDepth(Sort::Descending)
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

    protected function startProcess(): void
    {
        foreach ($this->items as $item) {
            $newName = Str::of($item->getBasename())
                ->when($this->ascii, fn ($str) => $str->ascii())
                ->replaceMatches($this->search, $this->replace)
                ->replaceMatches('/./', function ($matches) {
                    return Str::isMatch($this->expected, $matches[0]) ? $matches[0] : $this->remainder;
                })->value();

            $from = $item->getRealPath();
            $to = join_paths($item->getPath(), $newName);

            $this->newLine();
            $this->comment("From: {$from}");
            $this->comment("To: {$to}");

            if (! $this->dryRun) {
                $res = File::move($from, $to);
                if ($res) {
                    $this->info("Renamed [{$item->getBasename()}] to [{$newName}]");
                } else {
                    $this->error("Failed to rename [{$item->getBasename()}] to [{$newName}]");
                    if (! $this->noStop) {
                        $this->comment('Process stopped due to error.');
                        break;
                    }
                }
            }
        }

        $this->newLine();
    }
}
