<?php










namespace Symfony\Component\HttpKernel\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;














final class ExceptionEvent extends RequestEvent
{
private \Throwable $throwable;
private bool $allowCustomResponseCode = false;

public function __construct(
HttpKernelInterface $kernel,
Request $request,
int $requestType,
\Throwable $e,
private bool $isKernelTerminating = false,
) {
parent::__construct($kernel, $request, $requestType);

$this->setThrowable($e);
}

public function getThrowable(): \Throwable
{
return $this->throwable;
}






public function setThrowable(\Throwable $exception): void
{
$this->throwable = $exception;
}




public function allowCustomResponseCode(): void
{
$this->allowCustomResponseCode = true;
}




public function isAllowingCustomResponseCode(): bool
{
return $this->allowCustomResponseCode;
}

public function isKernelTerminating(): bool
{
return $this->isKernelTerminating;
}
}
