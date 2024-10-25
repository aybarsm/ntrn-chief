<?php










namespace Symfony\Component\Console\Descriptor;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;






class ApplicationDescription
{
public const GLOBAL_NAMESPACE = '_global';

private array $namespaces;




private array $commands;




private array $aliases = [];

public function __construct(
private Application $application,
private ?string $namespace = null,
private bool $showHidden = false,
) {
}

public function getNamespaces(): array
{
if (!isset($this->namespaces)) {
$this->inspectApplication();
}

return $this->namespaces;
}




public function getCommands(): array
{
if (!isset($this->commands)) {
$this->inspectApplication();
}

return $this->commands;
}




public function getCommand(string $name): Command
{
if (!isset($this->commands[$name]) && !isset($this->aliases[$name])) {
throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
}

return $this->commands[$name] ?? $this->aliases[$name];
}

private function inspectApplication(): void
{
$this->commands = [];
$this->namespaces = [];

$all = $this->application->all($this->namespace ? $this->application->findNamespace($this->namespace) : null);
foreach ($this->sortCommands($all) as $namespace => $commands) {
$names = [];


foreach ($commands as $name => $command) {
if (!$command->getName() || (!$this->showHidden && $command->isHidden())) {
continue;
}

if ($command->getName() === $name) {
$this->commands[$name] = $command;
} else {
$this->aliases[$name] = $command;
}

$names[] = $name;
}

$this->namespaces[$namespace] = ['id' => $namespace, 'commands' => $names];
}
}

private function sortCommands(array $commands): array
{
$namespacedCommands = [];
$globalCommands = [];
$sortedCommands = [];
foreach ($commands as $name => $command) {
$key = $this->application->extractNamespace($name, 1);
if (\in_array($key, ['', self::GLOBAL_NAMESPACE], true)) {
$globalCommands[$name] = $command;
} else {
$namespacedCommands[$key][$name] = $command;
}
}

if ($globalCommands) {
ksort($globalCommands);
$sortedCommands[self::GLOBAL_NAMESPACE] = $globalCommands;
}

if ($namespacedCommands) {
ksort($namespacedCommands, \SORT_STRING);
foreach ($namespacedCommands as $key => $commandsSet) {
ksort($commandsSet);
$sortedCommands[$key] = $commandsSet;
}
}

return $sortedCommands;
}
}
