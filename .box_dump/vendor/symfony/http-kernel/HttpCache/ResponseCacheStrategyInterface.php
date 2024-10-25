<?php














namespace Symfony\Component\HttpKernel\HttpCache;

use Symfony\Component\HttpFoundation\Response;







interface ResponseCacheStrategyInterface
{



public function add(Response $response): void;




public function update(Response $response): void;
}
