<?php

namespace App\Framework\Commands;

class Command extends \LaravelZero\Framework\Commands\Command
{
    public function task(string $title = '', $task = null, $loadingText = 'loading...'): bool
    {
        return $this->__call('task', func_get_args());
    }
}
