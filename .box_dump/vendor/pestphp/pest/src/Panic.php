<?php

declare(strict_types=1);

namespace Pest;

use NunoMaduro\Collision\Writer;
use Pest\Support\Container;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Whoops\Exception\Inspector;

final class Panic
{



private function __construct(
private readonly Throwable $throwable
) {

}




public static function with(Throwable $throwable): never
{
$panic = new self($throwable);

$panic->handle();

exit(1);
}




private function handle(): void
{
try {
$output = Container::getInstance()->get(OutputInterface::class);
} catch (Throwable) { 
$output = new ConsoleOutput;
}

assert($output instanceof OutputInterface);

if ($this->throwable instanceof Contracts\Panicable) {
$this->throwable->render($output);

exit($this->throwable->exitCode());
}

$writer = new Writer(null, $output);

$inspector = new Inspector($this->throwable);

$writer->write($inspector);
$output->writeln('');

exit(1);
}
}
