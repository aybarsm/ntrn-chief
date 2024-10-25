<?php













namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;






interface StoreInterface
{



public function lookup(Request $request): ?Response;









public function write(Request $request, Response $response): string;




public function invalidate(Request $request): void;






public function lock(Request $request): bool|string;






public function unlock(Request $request): bool;






public function isLocked(Request $request): bool;






public function purge(string $url): bool;




public function cleanup(): void;
}
