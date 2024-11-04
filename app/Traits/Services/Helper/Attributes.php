<?php

namespace App\Traits\Services\Helper;

use App\Attributes\Console\CommandTask;
use App\Contracts\Console\TaskingCommandContract;
use Illuminate\Support\Str;

trait Attributes
{
    public static function getCommandTaskAttributes(TaskingCommandContract $object): array
    {
        $result = [];

        $reflection = new \ReflectionObject($object);
        $attributes = $reflection->getAttributes(CommandTask::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            throw_if(! method_exists($object, $instance->method),
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
