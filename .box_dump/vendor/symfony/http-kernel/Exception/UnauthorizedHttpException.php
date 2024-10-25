<?php










namespace Symfony\Component\HttpKernel\Exception;




class UnauthorizedHttpException extends HttpException
{



public function __construct(string $challenge, string $message = '', ?\Throwable $previous = null, int $code = 0, array $headers = [])
{
$headers['WWW-Authenticate'] = $challenge;

parent::__construct(401, $message, $previous, $headers, $code);
}
}
