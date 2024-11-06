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

        public static function getAttributeList(object $object, string $attribute): array
    {
        throw_if(! class_exists($attribute), "Attribute class [{$attribute}] does not exist.");
        throw_if(! defined("{$attribute}::BIND"), "Attribute class [{$attribute}] does not have a BIND constant.");

        $bind = Str::lower($attribute::BIND);

        $result = [];

        $reflection = new \ReflectionObject($object);
        $attributes = $reflection->getAttributes($attribute);

        foreach ($attributes as $attrPos => $attr) {
            if ($bind == 'method') {
                throw_if(! property_exists($attribute, 'method'), "Attribute [{$attribute}] does not have a method property.");
            }

            if (method_exists($attribute, 'beforeResolve')) {
                $instance::beforeResolve($attrPos, $attr, $object);
            }

            $instance = $attr->newInstance();

            if ($bind == 'method') {
                throw_if(! method_exists($object, $instance->method), "Method [{$instance->method}] does not exist on [{$reflection->getName()}]");
            }

            if (method_exists($instance, 'afterResolve')) {
                $instance->afterResolve($attrPos, $attr, $object);
            }

            $result[] = $instance;
        }

        return $result;
    }
}
