<?php










namespace Symfony\Component\HttpFoundation\RateLimiter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimit;









interface RequestRateLimiterInterface
{
public function consume(Request $request): RateLimit;

public function reset(Request $request): void;
}
