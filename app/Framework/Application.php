<?php

namespace App\Framework;
use Illuminate\Foundation\Configuration\ApplicationBuilder;
class Application extends \LaravelZero\Framework\Application
{
    public static function configure(?string $basePath = null): ApplicationBuilder
    {
        $basePath = match (true) {
            is_string($basePath) => $basePath,
            default => static::inferBasePath(),
        };

        $builder = (new ApplicationBuilder(new static($basePath))); // @phpstan-ignore-line

        $builder->create()->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \App\Framework\Kernel::class
        );

        $builder->create()->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Illuminate\Foundation\Exceptions\Handler::class
        );

        return $builder
            ->withEvents()
            ->withCommands()
            ->withProviders();
    }
}
