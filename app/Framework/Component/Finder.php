<?php

namespace App\Framework\Component;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Finder\Finder as SymfonyFinder;
class Finder extends SymfonyFinder
{
    use Macroable, Conditionable;
}
