<?php

namespace Illuminate\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ThrottleRequestsException extends TooManyRequestsHttpException
{









public function __construct($message = '', ?Throwable $previous = null, array $headers = [], $code = 0)
{
parent::__construct(null, $message, $previous, $code, $headers);
}
}
