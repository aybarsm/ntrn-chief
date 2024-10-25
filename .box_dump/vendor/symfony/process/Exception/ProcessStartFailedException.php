<?php










namespace Symfony\Component\Process\Exception;

use Symfony\Component\Process\Process;




class ProcessStartFailedException extends ProcessFailedException
{
private Process $process;

public function __construct(Process $process, ?string $message)
{
if ($process->isStarted()) {
throw new InvalidArgumentException('Expected a process that failed during startup, but the given process was started successfully.');
}

$error = sprintf('The command "%s" failed.'."\n\nWorking directory: %s\n\nError: %s",
$process->getCommandLine(),
$process->getWorkingDirectory(),
$message ?? 'unknown'
);


RuntimeException::__construct($error);

$this->process = $process;
}

public function getProcess(): Process
{
return $this->process;
}
}
