<?php

namespace App\Framework\Commands;

use App\Contracts\Console\TaskingCommandContract;
use App\Enums\IndicatorType;
use App\Prompts\Note;
use App\Prompts\Progress;
use App\Prompts\Spinner;
use Laravel\Prompts\Spinner as LaravelSpinner;
use App\Services\Console\Task;
use App\Traits\Command\SignalHandler;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Laravel\Prompts\note as laravelNote;
abstract class TaskingCommand extends Command implements TaskingCommandContract, SignalableCommandInterface
{
    use SignalHandler;
    protected array $tasks;
    protected int $currentTask;
    protected Spinner|Progress|null $indicator = null;

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
        \Laravel\Prompts\Prompt::setOutput($output);

        return parent::run($input, $output);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        \Laravel\Prompts\Prompt::setOutput($output);

        return parent::execute($input, $output);
    }

    protected function executeTasks(): void
    {
        $cursor = new Cursor($this->output, $this->input);
////        dump($cursor);
//        $cursor->savePosition();
////        dump($cursor);
//        laravelNote(
//            message: 'Tasking Command',
//            type: 'intro',
//        );
//        sleep(2);
//        $cursor->restorePosition();
////        dump($cursor);
//        $cursor->clearOutput();


        foreach ($this->tasks as $taskId => $task) {
            $cursor = new Cursor($this->output, $this->input);
            $cursor->savePosition();

            $this->currentTask = $taskId;
            $this->output->writeln("Task [{$taskId}] {$task->title}: <comment>Running...</comment>");

            $this->indicator = match($task->indicatorType) {
                IndicatorType::SPINNER => new Spinner(message: $task->title),
                IndicatorType::PROGRESS => new Progress(label: $task->title),
                default => null,
            };

            $result = match($task->indicatorType) {
                IndicatorType::SPINNER => $this->indicator->spin(fn () => $this->{$task->method}()),
                default =>  $this->{$task->method}(),
            };

            $cursor->restorePosition();
            $cursor->clearOutput();

            $this->output->writeln(
                "Task [{$taskId}] {$task->title}: ".($result ? '<info>✔</info>' : '<error>failed</error>')
            );
        }
    }

}
