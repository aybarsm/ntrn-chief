<?php

namespace App\Framework\Commands;

use App\Contracts\Console\TaskingCommandContract;
use App\Enums\IndicatorType;
use App\Prompts\Progress;
use App\Prompts\Spinner;
use Laravel\Prompts\Spinner as LaravelSpinner;
use App\Services\Console\Task;
use App\Traits\Command\SignalHandler;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
abstract class TaskingCommand extends Command implements TaskingCommandContract, SignalableCommandInterface
{
    use SignalHandler;
    protected array $tasks;
    protected int $currentTask;
    protected int $currentCursorX;
    protected int $currentCursorY;
    protected Spinner|Progress|null $indicator = null;

    protected Cursor $cursor;

    public function __construct()
    {
        parent::__construct();

        $this->tasks = Task::getCommandTasks($this);

        $this->setSignalHandler('SIGINT', function (...$params) {
            $this->error('Command interrupted');
        });
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->cursor = new Cursor($output);

        return parent::run($input, $output);
    }

    protected function executeTasks(): void
    {
//        \Laravel\Prompts\Prompt::theme('default');

        foreach ($this->tasks as $taskId => $task) {
            $this->currentTask = $taskId;
            [$this->currentCursorX, $this->currentCursorY] = $this->cursor->getCurrentPosition();
            info("Task [$taskId]: Begin", [$this->currentCursorX, $this->currentCursorY]);
//            $this->
//            $this->output->write("Task [$taskId] <comment>{$task->title}</comment> : Started");
//            $this->newLine();
            $this->info("Task [$taskId]: Started");
            $this->newLine();
//            sleep(1);
//            $this->output->write("Task [$taskId]: <comment>{$task->title}</comment>");
//            $this->info("Task [$taskId]: <comment>{$task->title}</comment>");

            $this->indicator = match($task->indicatorType) {
                IndicatorType::SPINNER => new Spinner(message: $task->title),
                IndicatorType::PROGRESS => new Progress(label: $task->title),
                default => null,
            };

            match($task->indicatorType) {
                IndicatorType::SPINNER => $this->indicator->spin(fn () => $this->{$task->method}()),
                default => null,
            };

            $this->cursor = $this->cursor->moveToPosition(1, $this->currentCursorY);
            $this->cursor = $this->cursor->clearLineAfter();
            $this->info("Task [$taskId]: Completed");
            $this->newLine();
            info("Task [$taskId]: End", [$this->currentCursorX, $this->currentCursorY]);
//            $this->cursor->clearLine();

//            $this->indicator = $this->getIndicator($task->indicator);
//            $this->indicator->start($task->title);
//
//            $this->{$task->method}();
//
//            $this->indicator->finish();
        }
    }

}
