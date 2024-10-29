<?php

namespace App\Services\Console;

use App\Attributes\Console\CommandTask;
use App\Contracts\Console\TaskingCommandContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Task
{
    public static function getCommandTasks(TaskingCommandContract $command): array
    {
        $result = [];

        $reflection = new \ReflectionObject($command);
        $attributes = $reflection->getAttributes(CommandTask::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            throw_if(! method_exists($command, $instance->method),
                new \Exception("Method [{$instance->method}] does not exist on [{$reflection->getName()}]")
            );

            if (blank($instance->title)) {
                $instance->title = Str::headline($instance->method);
            }

            $result[] = $instance;
        }

        return $result;
    }

}
