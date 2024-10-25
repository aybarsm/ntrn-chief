<?php










namespace Symfony\Component\HttpFoundation\RateLimiter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimit;
















interface PeekableRequestRateLimiterInterface extends RequestRateLimiterInterface
{
public function peek(Request $request): RateLimit;
}
