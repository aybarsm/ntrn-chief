<?php

namespace App\Framework\Commands;

use App\Contracts\Console\TaskingCommandContract;
use App\Prompts\Progress;
use App\Prompts\Spinner;
use App\Services\Console\Task;
abstract class TaskingCommand extends Command implements TaskingCommandContract
{
    protected array $tasks;
    protected int $currentTask;
    protected Spinner|Progress|null $indicator = null;

    public function __construct()
    {
        parent::__construct();

        $this->tasks = Task::getCommandTasks($this);
    }

}
