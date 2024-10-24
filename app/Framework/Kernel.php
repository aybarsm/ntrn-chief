<?php

namespace App\Framework;

use Illuminate\Console\Application as Artisan;

class Kernel extends \LaravelZero\Framework\Kernel
{
    protected function commands(): void
    {
        $config = $this->app['config'];

        $this->load($config->get('commands.paths', $this->app->path('Commands')));

        $commands = collect($config->get('commands.add', []))->merge(
            $config->get('commands.hidden', $this->hiddenCommands),
        );

        if ($command = $config->get('commands.default')) {
            $commands->push($command);
        }

        if ($this->app->environment() !== 'production') {
            $commands = $commands->merge($this->developmentCommands);
        }

        $toRemoveCommands = $config->get('commands.remove', []);

        $toRemoveCommands = array_merge($toRemoveCommands, $this->developmentOnlyCommands);

        $commands = $commands->diff($toRemoveCommands);

        Artisan::starting(
            function ($artisan) use ($toRemoveCommands) {
                $reflectionClass = new \ReflectionClass(Artisan::class);
                $commands = collect($artisan->all())
                    ->filter(
                        fn ($command) => ! in_array(get_class($command), $toRemoveCommands, true)
                    )
                    ->toArray();

                $property = $reflectionClass->getParentClass()
                    ->getProperty('commands');

                $property->setAccessible(true);
                $property->setValue($artisan, $commands);
                $property->setAccessible(false);
            }
        );

        /*
         * Registers a bootstrap callback on the artisan console application
         * in order to call the schedule method on each Laravel Zero
         * command class.
         */
        Artisan::starting(
            function ($artisan) use ($commands) {
                $artisan->resolveCommands($commands->toArray());

                $artisan->setContainerCommandLoader();
            }
        );

        Artisan::starting(
            function ($artisan) use ($config) {
                $commands = array_merge(
                    $config->get('commands.hidden'),
                    $this->hiddenCommands,
                );

                collect($artisan->all())->each(
                    function ($command) use ($artisan, $commands) {
                        if (in_array(get_class($command), $commands, true)) {
                            $command->setHidden(true);
                        }

                        $command->setApplication($artisan);

                        if ($command instanceof \LaravelZero\Framework\Commands\Command) {
                            $this->app->call([$command, 'schedule']);
                        }
                    }
                );
            }
        );
    }
}
