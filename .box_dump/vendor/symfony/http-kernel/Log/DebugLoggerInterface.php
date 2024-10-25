<?php










namespace Symfony\Component\HttpKernel\Log;

use Symfony\Component\HttpFoundation\Request;






interface DebugLoggerInterface
{













public function getLogs(?Request $request = null): array;




public function countErrors(?Request $request = null): int;




public function clear(): void;
}
