<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






class AjaxDataCollector extends DataCollector
{
public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
{

}

public function reset(): void
{

}

public function getName(): string
{
return 'ajax';
}
}
