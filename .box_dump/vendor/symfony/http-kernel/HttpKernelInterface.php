<?php










namespace Symfony\Component\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






interface HttpKernelInterface
{
public const MAIN_REQUEST = 1;
public const SUB_REQUEST = 2;













public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response;
}
