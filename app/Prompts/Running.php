<?php

namespace App\Prompts;

use App\Prompts\Contracts\RunningContract;
use App\Traits\ConfigableOpen;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;

class Running extends Prompt implements RunningContract
{
    use Conditionable, ConfigableOpen, Macroable;

    /**
     * @inheritDoc
     */
    public function value(): mixed
    {
        return true;
    }
}
