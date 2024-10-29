<?php

namespace App\Services\Console;

use App\Attributes\Console\CommandTask;
use App\Contracts\Console\TaskingCommandContract;
use Illuminate\Support\Collection;

class Task
{
    public static function getCommandTasks(TaskingCommandContract $command): Collection
    {
        $result = collect();

        $reflection = new \ReflectionObject($command);

        foreach ($reflection->getMethods() as $method) {
            $attribute = $method->getAttributes(CommandTask::class);

            if (count($attribute) == 0) {
                continue;
            }

            $attribute = $attribute[0];

            $task = $attribute->newInstance();
            dump($attribute->getName());
            dump($attribute->getTarget());
            dump($attribute->getArguments());
//            dump($task);
        }

        return $result;

//        foreach ($reflection->getMethods() as $method) {
//            $attributes = $method->getAttributes(CommandTask::class);
//
//            if (count($attributes) < 2) {
//                continue;
//            }
//        }
    }

}
