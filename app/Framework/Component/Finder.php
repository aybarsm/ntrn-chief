<?php

namespace App\Framework\Component;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Ramsey\Collection\Sort;
use Symfony\Component\Finder\Finder as SymfonyFinder;

class Finder extends SymfonyFinder
{
    use Conditionable, Macroable;

    public function sortByDepth(Sort $order = Sort::Ascending): static
    {
        $this->sort(static function (\SplFileInfo $a, \SplFileInfo $b) use ($order) {
            return $order === Sort::Ascending ? $a->getRealPath() <=> $b->getRealPath() : $b->getRealPath() <=> $a->getRealPath();
        });

        return $this;
    }
}
