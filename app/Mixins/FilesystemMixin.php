<?php

namespace App\Mixins;

use App\Framework\Component\Finder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Collection\Sort;

use function Illuminate\Filesystem\join_paths;

/** @mixin \Illuminate\Filesystem\Filesystem */
class FilesystemMixin
{
    const string BIND = \Illuminate\Filesystem\Filesystem::class;

    public static function matching(): \Closure
    {
        return function (
            string $path,
            string $pattern,
            bool $dirs = false,
            bool $recursive = false,
            bool $hidden = false,
            bool $basename = true,
            ?Sort $sort = null,
            bool $returnFullPath = true,
            bool $values = true,
            bool $asArray = true,
            bool $negateMatch = false
        ): array|Collection {
            $entries = match (true) {
                $dirs && $recursive => $this->allDirectories($path),
                $dirs => $this->directories($path),
                $recursive => $this->allFiles($path, $hidden),
                default => $this->files($path, $hidden),
            };

            return collect($entries)
                ->when($basename, fn (Collection $items) => $items->map(fn ($item) => $dirs ? basename($item) : $item->getFilename()))
                ->filter(fn ($item) => $negateMatch ? ! Str::isMatch($pattern, $item) : Str::isMatch($pattern, $item))
                ->when($sort === Sort::Ascending, fn ($items) => $items->sort())
                ->when($sort === Sort::Descending, fn ($items) => $items->sortDesc())
                ->when($basename && $returnFullPath, fn ($items) => $items->map(fn ($item) => join_paths($path, $item)))
                ->when($values, fn ($dirs) => $dirs->values())
                ->when($asArray, fn ($dirs) => $dirs->toArray());
        };
    }

    public static function notMatching(): \Closure
    {
        return function (
            string $path,
            string $pattern,
            bool $dirs = false,
            bool $recursive = false,
            bool $hidden = false,
            bool $basename = true,
            ?Sort $sort = null,
            bool $returnFullPath = true,
            bool $values = true,
            bool $asArray = true
        ): array|Collection {
            return static::matching($path, $pattern, $dirs, $recursive, $hidden, $basename, $sort, $returnFullPath, $values, $asArray, true);
        };
    }

    public static function allDirectories(): \Closure
    {
        return function ($directory): array {
            $directories = [];

            foreach (Finder::create()->in($directory)->directories()->sortByName() as $dir) {
                $directories[] = $dir->getRealPath();
            }

            return $directories;
        };
    }

    public static function finder(): \Closure
    {
        return function (?string $in = null): Finder {
            $finder = Finder::create();

            return blank($in) ? $finder : $finder->in($in);
        };
    }

    public static function in(): \Closure
    {
        return function (string $in): Finder {
            return static::finder($in);
        };
    }
}
