<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






class ExceptionDataCollector extends DataCollector
{
public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
{
if (null !== $exception) {
$this->data = [
'exception' => FlattenException::createWithDataRepresentation($exception),
];
}
}

public function hasException(): bool
{
return isset($this->data['exception']);
}

public function getException(): \Exception|FlattenException
{
return $this->data['exception'];
}

public function getMessage(): string
{
return $this->data['exception']->getMessage();
}

public function getCode(): int
{
return $this->data['exception']->getCode();
}

public function getStatusCode(): int
{
return $this->data['exception']->getStatusCode();
}

public function getTrace(): array
{
return $this->data['exception']->getTrace();
}

public function getName(): string
{
return 'exception';
}
}
