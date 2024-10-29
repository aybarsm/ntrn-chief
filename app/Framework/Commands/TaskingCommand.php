<?php

namespace App\Framework\Commands;

use App\Prompts\Progress;
use App\Prompts\Spinner;
use Illuminate\Support\Collection;
use App\Attributes\Console\CommandTask;
abstract class TaskingCommand extends Command
{
    protected Collection $tasks;
    protected int $currentTask;
    protected Spinner|Progress|null $indicator = null;

    public function __construct()
    {
        parent::__construct();
    }

//    protected function buildTasks()
//    {
//        $reflection = new \ReflectionObject($actionHandler);
//    }
}
