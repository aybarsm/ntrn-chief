<?php










namespace Symfony\Component\Process\Exception;

use Symfony\Component\Process\Process;






final class ProcessSignaledException extends RuntimeException
{
private Process $process;

public function __construct(Process $process)
{
$this->process = $process;

parent::__construct(sprintf('The process has been signaled with signal "%s".', $process->getTermSignal()));
}

public function getProcess(): Process
{
return $this->process;
}

public function getSignal(): int
{
return $this->getProcess()->getTermSignal();
}
}
