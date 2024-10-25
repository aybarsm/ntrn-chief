<?php

namespace Illuminate\Console\View\Components;

use Illuminate\Support\InteractsWithTime;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Termwind\terminal;

class Task extends Component
{
use InteractsWithTime;









public function render($description, $task = null, $verbosity = OutputInterface::VERBOSITY_NORMAL)
{
$description = $this->mutate($description, [
Mutators\EnsureDynamicContentIsHighlighted::class,
Mutators\EnsureNoPunctuation::class,
Mutators\EnsureRelativePaths::class,
]);

$descriptionWidth = mb_strlen(preg_replace("/\<[\w=#\/\;,:.&,%?]+\>|\\e\[\d+m/", '$1', $description) ?? '');

$this->output->write("  $description ", false, $verbosity);

$startTime = microtime(true);

$result = false;

try {
$result = ($task ?: fn () => true)();
} catch (Throwable $e) {
throw $e;
} finally {
$runTime = $task
? (' '.$this->runTimeForHumans($startTime))
: '';

$runTimeWidth = mb_strlen($runTime);
$width = min(terminal()->width(), 150);
$dots = max($width - $descriptionWidth - $runTimeWidth - 10, 0);

$this->output->write(str_repeat('<fg=gray>.</>', $dots), false, $verbosity);
$this->output->write("<fg=gray>$runTime</>", false, $verbosity);

$this->output->writeln(
$result !== false ? ' <fg=green;options=bold>DONE</>' : ' <fg=red;options=bold>FAIL</>',
$verbosity,
);
}
}
}
