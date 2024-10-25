<?php

namespace Illuminate\Database;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Arr;
use InvalidArgumentException;

abstract class Seeder
{





protected $container;






protected $command;






protected static $called = [];









public function call($class, $silent = false, array $parameters = [])
{
$classes = Arr::wrap($class);

foreach ($classes as $class) {
$seeder = $this->resolve($class);

$name = get_class($seeder);

if ($silent === false && isset($this->command)) {
with(new TwoColumnDetail($this->command->getOutput()))->render(
$name,
'<fg=yellow;options=bold>RUNNING</>'
);
}

$startTime = microtime(true);

$seeder->__invoke($parameters);

if ($silent === false && isset($this->command)) {
$runTime = number_format((microtime(true) - $startTime) * 1000);

with(new TwoColumnDetail($this->command->getOutput()))->render(
$name,
"<fg=gray>$runTime ms</> <fg=green;options=bold>DONE</>"
);

$this->command->getOutput()->writeln('');
}

static::$called[] = $class;
}

return $this;
}








public function callWith($class, array $parameters = [])
{
$this->call($class, false, $parameters);
}








public function callSilent($class, array $parameters = [])
{
$this->call($class, true, $parameters);
}








public function callOnce($class, $silent = false, array $parameters = [])
{
if (in_array($class, static::$called)) {
return;
}

$this->call($class, $silent, $parameters);
}







protected function resolve($class)
{
if (isset($this->container)) {
$instance = $this->container->make($class);

$instance->setContainer($this->container);
} else {
$instance = new $class;
}

if (isset($this->command)) {
$instance->setCommand($this->command);
}

return $instance;
}







public function setContainer(Container $container)
{
$this->container = $container;

return $this;
}







public function setCommand(Command $command)
{
$this->command = $command;

return $this;
}









public function __invoke(array $parameters = [])
{
if (! method_exists($this, 'run')) {
throw new InvalidArgumentException('Method [run] missing from '.get_class($this));
}

$callback = fn () => isset($this->container)
? $this->container->call([$this, 'run'], $parameters)
: $this->run(...$parameters);

$uses = array_flip(class_uses_recursive(static::class));

if (isset($uses[WithoutModelEvents::class])) {
$callback = $this->withoutModelEvents($callback);
}

return $callback();
}
}
