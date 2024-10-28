<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function boot(): void
    {

    }

    public function register(): void
    {
        Http::macro('progress', function ($callback) {
            return $this->withOptions([
                'progress' => fn (...$parameters) => $callback(...$parameters),
            ]);
        });

//        App::booted(function () {
//            Command::macro(
//                'task',
//                function (string $title, $task = null, $loadingText = 'loading ZZZZ...') {
//                    $this->output->write("$title: <comment>{$loadingText}</comment>");
//
//                    if ($task === null) {
//                        $result = true;
//                    } else {
//                        try {
//                            $result = $task() === false ? false : true;
//                        } catch (\Exception $taskException) {
//                            $result = false;
//                        }
//                    }
//
//                    if ($this->output->isDecorated()) { // Determines if we can use escape sequences
//                        // Move the cursor to the beginning of the line
//                        $this->output->write("\x0D");
//
//                        // Erase the line
//                        $this->output->write("\x1B[2K");
//                    } else {
//                        $this->output->writeln(''); // Make sure we first close the previous line
//                    }
//
//                    $this->output->writeln(
//                        "$title: ".($result ? '<info>✔</info>' : '<error>failed</error>')
//                    );
//
//                    if (isset($taskException)) {
//                        throw $taskException;
//                    }
//
//                    return $result;
//                }
//            );
//        });
    }
}
