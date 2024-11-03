<?php

namespace App\Framework\Commands;

use App\Contracts\Console\TaskingCommandContract;
use App\Enums\IndicatorType;
use App\Prompts\Progress;
use App\Prompts\Spinner;
use App\Services\Helper;
use App\Traits\Command\SignalHandler;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Cursor;

abstract class TaskingCommand extends Command implements SignalableCommandInterface, TaskingCommandContract
{
    use SignalHandler;

    protected array $tasks;

    protected int $currentTask;

    protected Cursor $cursor;

    protected string $taskMessageTitle = '';

    protected array $taskMessages = [];

    protected Spinner|Progress|null $indicator = null;

    public function __construct()
    {
        parent::__construct();
    }

    protected function setTaskMessage(string $message): void
    {
        if (! isset($this->taskMessages[$this->currentTask])) {
            $this->taskMessages[$this->currentTask] = [];
        }

        $this->taskMessages[$this->currentTask][] = $message;
    }

    protected function executeTasks(): void
    {
        $this->tasks = Helper::getCommandTasks($this);
        $this->cursor = new Cursor($this->output);
        \App\Prompts\Prompt::setOutput($this->output);
        \App\Prompts\Prompt::setCursor($this->cursor);

        foreach ($this->tasks as $taskId => $task) {
            $cursorPos = $this->cursor->getCurrentPosition();

            $this->currentTask = $taskId;
            $taskOrder = $taskId + 1;
            $this->taskMessageTitle = "{$taskOrder}. {$task->title} task messages:";
            $this->output->writeln("{$taskOrder}. {$task->title}: <comment>Running...</comment>");

            if ($task->indicatorType == IndicatorType::PROGRESS) {
                $this->output->writeln('');
            }

            $this->indicator = match ($task->indicatorType) {
                IndicatorType::SPINNER => new Spinner(message: $task->title),
                IndicatorType::PROGRESS => new Progress(label: $task->title),
                default => null,
            };

            try {
                $result = match ($task->indicatorType) {
                    IndicatorType::SPINNER => $this->indicator->spin(fn () => $this->{$task->method}()),
                    default => $this->{$task->method}(),
                };
            } catch (\Exception $taskException) {
                $result = false;
            }

            $this->cursor->moveToPosition($cursorPos[0], $cursorPos[1] - 1);
            $this->cursor->clearOutput();

            $msgSuffix = ($result === false ? ('<error>Failed'.($task->explicit ? ' Explicitly' : '').'!</error>') : '<info>Completed</info>');
            $this->output->writeln("{$taskOrder}. {$task->title}: {$msgSuffix}");

            $taskMessages = $this->taskMessages[$this->currentTask] ?? [];
            if (! blank($taskMessages)) {
                $this->taskMessageTitle = Str::of($this->taskMessageTitle)->trim()->start("{$taskOrder}. {$task->title} ")->value();
                $this->output->writeln($this->taskMessageTitle);
                $this->output->listing($taskMessages);
                $this->cursor->moveUp(1);
                $this->cursor->clearLine();
            }

            if ($result === false) {
                if (isset($taskException)) {
                    throw $taskException;
                }
                if ($task->explicit) {
                    break;
                }
            }

            if ($result === null && $task->skipRest) {
                break;
            }
        }
    }
}
