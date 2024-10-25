<?php










namespace Symfony\Component\HttpKernel\Exception;




class PreconditionFailedHttpException extends HttpException
{
public function __construct(string $message = '', ?\Throwable $previous = null, int $code = 0, array $headers = [])
{
parent::__construct(412, $message, $previous, $headers, $code);
}
}
