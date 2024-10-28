<?php

namespace App\Traits\Command;
use App\Services\Console\Indicator;

trait Indicatorable
{
    protected ?Indicator $indicator = null;

    protected function indicator(...$params): Indicator
    {
        if ($this->indicator === null) {
            $this->indicator = new Indicator(...$params);
        }

        return $this->indicator;
    }

}
