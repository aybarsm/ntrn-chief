<?php

namespace App\Mixins;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ramsey\Collection\Sort;
use function Illuminate\Filesystem\join_paths;

/** @mixin \Illuminate\Filesystem\Filesystem */
class FilesystemMixin
{
    const string BIND = \Illuminate\Filesystem\Filesystem::class;

    public function matching(): \Closure
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
        ): array|Collection
        {
            $entries = match(true) {
                $dirs => $this->directories($path),
                $recursive => $this->allFiles($path, $hidden),
                default => $this->files($path, $hidden),
            };

            return collect($entries)
                ->when($basename, fn ($items) => $items->map(fn ($item) => $dirs ? basename($item) : $item->getFilename()))
                ->filter(fn ($item) => Str::isMatch($pattern, $item))
                ->when($sort === Sort::Ascending, fn ($items) => $items->sort())
                ->when($sort === Sort::Descending, fn ($items) => $items->sortDesc())
                ->when($basename && $returnFullPath, fn ($items) => $items->map(fn ($item) => join_paths($path, $item)))
                ->when($values, fn ($dirs) => $dirs->values())
                ->when($asArray, fn ($dirs) => $dirs->toArray());
        };
    }
}
