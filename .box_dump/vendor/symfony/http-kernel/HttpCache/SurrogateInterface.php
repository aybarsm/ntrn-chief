<?php










namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface SurrogateInterface
{



public function getName(): string;




public function createCacheStrategy(): ResponseCacheStrategyInterface;




public function hasSurrogateCapability(Request $request): bool;




public function addSurrogateCapability(Request $request): void;






public function addSurrogateControl(Response $response): void;




public function needsParsing(Response $response): bool;







public function renderIncludeTag(string $uri, ?string $alt = null, bool $ignoreErrors = true, string $comment = ''): string;




public function process(Request $request, Response $response): Response;









public function handle(HttpCache $cache, string $uri, string $alt, bool $ignoreErrors): string;
}
