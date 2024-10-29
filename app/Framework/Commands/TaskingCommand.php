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
            $this->output->section("Task [$taskId] {$task->title} : Started");
            $this->output->success("Task [$taskId] {$task->title} : Completed");
            $this->output->error("Task [$taskId] {$task->title} : Failed");

//            $note = new Note(
//                message: "Task [$taskId] {$task->title} : Started",
//                type: 'intro',
//            );
//            $note->display();
//            $note->clear();
//
//            return;

//            info("BEGIN Lines: {$this->output->newLinesWritten()}");
//            $this->output->write("$title: <comment>{$loadingText}</comment>");
//            [$this->currentCursorX, $this->currentCursorY] = $this->cursor->getCurrentPosition();
//            info("Task [$taskId]: Begin", [$this->currentCursorX, $this->currentCursorY]);
//            $this->
//            $this->output->write("Task [$taskId] <comment>{$task->title}</comment> : Started");
//            $this->output->writeln('');
//            $this->getOutput()->writeDirectly();
//            $this->output->section("Task [$taskId] {$task->title} : Started");
//            $this->output->writeln("Task [$taskId] {$task->title} : Started");
//            $this->newLine();
//            $this->info("Task [$taskId]: Started");
//            $this->newLine();
//            sleep(1);
//            $this->output->write("Task [$taskId]: <comment>{$task->title}</comment>");
//            $this->info("Task [$taskId]: <comment>{$task->title}</comment>");

            $this->indicator = match($task->indicatorType) {
                IndicatorType::SPINNER => new Spinner(message: $task->title),
                IndicatorType::PROGRESS => new Progress(label: $task->title),
                default => null,
            };

//            dump($this->indicator);
//
////            $this->indicator->config('set', 'pre.message', "Task [$taskId] [{$task->title}] : Started");
//
            $result = match($task->indicatorType) {
                IndicatorType::SPINNER => $this->indicator->spin(fn () => $this->{$task->method}()),
                default => null,
            };

//            $note->clear();
//            $note->message = "Task [$taskId] {$task->title} : Completed";
//            $note->type = 'outro';
//            $note->display();

//            info("BEGIN END: {$this->output->newLinesWritten()}");
//            dump($this->indicator);

//
//            $this->info("Task [$taskId]: Completed");
//            $this->indicator->eraseRenderedLines();

//            dump($this->indicator);

//            $this->cursor = $this->cursor->moveToPosition(1, $this->currentCursorY);
//            $this->cursor = $this->cursor->clearLineAfter();
//            $this->info("Task [$taskId]: Completed");
//            $this->newLine();
//            info("Task [$taskId]: End", [$this->currentCursorX, $this->currentCursorY]);
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
