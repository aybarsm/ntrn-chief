<?php










namespace Symfony\Component\HttpKernel\Exception;






interface HttpExceptionInterface extends \Throwable
{



public function getStatusCode(): int;




public function getHeaders(): array;
}
