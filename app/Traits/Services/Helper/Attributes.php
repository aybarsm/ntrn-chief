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
        throw_if(! defined("{$attribute}::EXPECT"), "Attribute class [{$attribute}] does not have an EXPECT constant.");

//        $expectMet = false;
//        foreach($attribute::EXPECT as $expect){
//            if ($object instanceof $expect){
//                $expectMet = true;
//                break;
//            }
//        }
//        $class = get_class($object);
//        throw_if(count($attribute::EXPECT) > 0 && ! $expectMet, "Object [{$class}] does not implement any of the expected interfaces.");

        $bind = Str::lower($attribute::BIND);

        $result = [];

        $reflection = new \ReflectionObject($object);
        $attributes = $reflection->getAttributes($attributeClass);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            if ($bind == 'method') {
                throw_if(! property_exists($instance, 'method'), "Attribute [{$attribute}] does not have a method property.");
                throw_if(! method_exists($object, $instance->method), "Method [{$instance->method}] does not exist on [{$reflection->getName()}]");
            }

            if (method_exists($instance, 'beforeResolve')) {
                $instance->beforeResolve($object);
            }

            if (method_exists($instance, 'afterResolve')) {
                $instance->afterResolve($object);
            }

            $result[] = $instance;
        }

        return $result;
    }
}
