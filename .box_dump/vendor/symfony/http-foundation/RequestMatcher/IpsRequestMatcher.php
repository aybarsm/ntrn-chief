<?php










namespace Symfony\Component\HttpFoundation\RequestMatcher;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;






class IpsRequestMatcher implements RequestMatcherInterface
{
private array $ips;





public function __construct(array|string $ips)
{
$this->ips = array_reduce((array) $ips, static fn (array $ips, string $ip) => array_merge($ips, preg_split('/\s*,\s*/', $ip)), []);
}

public function matches(Request $request): bool
{
if (!$this->ips) {
return true;
}

return IpUtils::checkIp($request->getClientIp() ?? '', $this->ips);
}
}
