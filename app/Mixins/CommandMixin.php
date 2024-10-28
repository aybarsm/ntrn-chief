<?php

namespace App\Mixins;

use Illuminate\Support\Str;

/** @mixin \Illuminate\Console\Command */
class CommandMixin
{
    public static function task(): \Closure
    {
        return function (string $title, $task = null, $indicator = null): string
        {
            $spinTitle = Str::of($title)->start('<info>')->finish('</info>')->value();

            if ($task === null) {
                $result = true;
            } else {
                try {
//                    $spinner = new \App\Framework\Prompts\Spinner($spinTitle);
//                    $result = $spinner->spin(fn () => $task()) === false ? false : true;
                } catch (\Throwable $taskException) {
                    $result = false;
                }
            }

            $this->output->writeln(
                "$title: ".($result ? '<info>✔</info>' : '<error>failed</error>')
            );

            if (isset($taskException)) {
                throw $taskException;
            }

            return $result;
        };
    }
}
