<?php










namespace Symfony\Component\Process\Exception;

use Symfony\Component\Process\Process;






class ProcessTimedOutException extends RuntimeException
{
public const TYPE_GENERAL = 1;
public const TYPE_IDLE = 2;

private Process $process;
private int $timeoutType;

public function __construct(Process $process, int $timeoutType)
{
$this->process = $process;
$this->timeoutType = $timeoutType;

parent::__construct(sprintf(
'The process "%s" exceeded the timeout of %s seconds.',
$process->getCommandLine(),
$this->getExceededTimeout()
));
}

public function getProcess(): Process
{
return $this->process;
}

public function isGeneralTimeout(): bool
{
return self::TYPE_GENERAL === $this->timeoutType;
}

public function isIdleTimeout(): bool
{
return self::TYPE_IDLE === $this->timeoutType;
}

public function getExceededTimeout(): ?float
{
return match ($this->timeoutType) {
self::TYPE_GENERAL => $this->process->getTimeout(),
self::TYPE_IDLE => $this->process->getIdleTimeout(),
default => throw new \LogicException(sprintf('Unknown timeout type "%d".', $this->timeoutType)),
};
}
}
