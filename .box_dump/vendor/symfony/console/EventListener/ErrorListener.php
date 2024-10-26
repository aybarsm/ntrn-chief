<?php










namespace Symfony\Component\Console\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;





class ErrorListener implements EventSubscriberInterface
{
public function __construct(
private ?LoggerInterface $logger = null,
) {
}

public function onConsoleError(ConsoleErrorEvent $event): void
{
if (null === $this->logger) {
return;
}

$error = $event->getError();

if (!$inputString = $this->getInputString($event)) {
$this->logger->critical('An error occurred while using the console. Message: "{message}"', ['exception' => $error, 'message' => $error->getMessage()]);

return;
}

$this->logger->critical('Error thrown while running command "{command}". Message: "{message}"', ['exception' => $error, 'command' => $inputString, 'message' => $error->getMessage()]);
}

public function onConsoleTerminate(ConsoleTerminateEvent $event): void
{
if (null === $this->logger) {
return;
}

$exitCode = $event->getExitCode();

if (0 === $exitCode) {
return;
}

if (!$inputString = $this->getInputString($event)) {
$this->logger->debug('The console exited with code "{code}"', ['code' => $exitCode]);

return;
}

$this->logger->debug('Command "{command}" exited with code "{code}"', ['command' => $inputString, 'code' => $exitCode]);
}

public static function getSubscribedEvents(): array
{
return [
ConsoleEvents::ERROR => ['onConsoleError', -128],
ConsoleEvents::TERMINATE => ['onConsoleTerminate', -128],
];
}

private static function getInputString(ConsoleEvent $event): ?string
{
$commandName = $event->getCommand()?->getName();
$input = $event->getInput();

if ($input instanceof \Stringable) {
if ($commandName) {
return str_replace(["'$commandName'", "\"$commandName\""], $commandName, (string) $input);
}

return (string) $input;
}

return $commandName;
}
}
