<?php

namespace App\Services;

use App\Attributes\TaskMethod;
use App\Contracts\TaskingMethodContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class TaskingMethod implements TaskingMethodContract
{
    protected array $tasks;
    protected bool $taskPreventThrow = false;
    protected bool $taskStopExecution = false;
    protected bool $tasksExecuted = false;

    protected function executeTasks(): void
    {
        if ($this->tasksExecuted) {
            return;
        }

        $this->tasks = Helper::getAttributeList($this, TaskMethod::class);
        $skipList = collect();

        foreach ($this->tasks as $taskPos => $task) {
            $this->taskPreventThrow = false;
            $this->taskStopExecution = false;
            $skip = $skipList->search(fn ($item) => $item['method'] == $task->method);
            if ($skip !== false) {
                if (method_exists($this, 'handleSkip')) {
                    $this->handleSkip($skipList->get($skip), $taskPos, $task);
                }
                $skipList->forget($skip);
                continue;
            }

            try{
                $this->{$task->method}();
            }catch (\Exception $exception){
                if (method_exists($this, 'handleException')) {
                    $this->handleException($taskPos, $task, $exception);
                }elseif (! $this->taskPreventThrow) {
                    throw $exception;
                }

                if ($task->bail) {
                    break;
                }elseif (count($task->whenFailedSkip) > 0) {
                    Arr::map($task->whenFailedSkip, function ($method) use ($taskPos, $task, $skipList) {
                        $skipList->when(
                            fn (Collection $list) => $list->firstWhere('method', $method) === null,
                            fn (Collection $list) => $list->push(['method' => $method, 'source' => ['taskPos' => $taskPos, 'task' => $task]])
                        );
                    });
                }
            }

            if ($this->taskStopExecution) {
                break;
            }
        }

        $this->tasksExecuted = true;
    }

}
