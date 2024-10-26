<?php

namespace App\Traits\Command;

use App\Traits\Configurable;

trait Taskable
{
    use Configurable;

    protected function taskRun(string $title, $task = null, $loadingText = 'loading...'): bool
    {
        $this->config('set', 'current.task', $title);

        $result = $this->task($title, $task, $loadingText);

        $this->config('set', 'current.task', null);

        return $result;
    }

    protected function taskComment(string $message): void
    {
        $title = $this->config('get', 'current.task');

        if ($title === null) {
            return;
        }

        if ($this->output->isDecorated()) {
            $this->output->write("\x0D");
            $this->output->write("\x1B[2K");
            $this->output->write("$title: <comment>{$message}</comment>");
        } else {
            $this->output->writeln(''); // Make sure we first close the previous line
        }
    }

}
