<?php

namespace App\Services;

use App\Attributes\TaskMethod;
use App\Contracts\TaskingMethodContract;

abstract class TaskingMethod implements TaskingMethodContract
{
    protected array $tasks;
    protected bool $tasksExecuted = false;

    protected function executeTasks(): void
    {
        if ($this->tasksExecuted) {
            return;
        }

        $this->tasks = Helper::getAttributeList($this, TaskMethod::class);

        $this->tasksExecuted = true;
    }

}
