<?php

namespace Illuminate\Http\Middleware;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class TrustHosts
{





protected $app;






protected static $alwaysTrust;






protected static $subdomains;







public function __construct(Application $app)
{
$this->app = $app;
}






public function hosts()
{
if (is_null(static::$alwaysTrust)) {
return [$this->allSubdomainsOfApplicationUrl()];
}

$hosts = match (true) {
is_array(static::$alwaysTrust) => static::$alwaysTrust,
is_callable(static::$alwaysTrust) => call_user_func(static::$alwaysTrust),
default => [],
};

if (static::$subdomains) {
$hosts[] = $this->allSubdomainsOfApplicationUrl();
}

return $hosts;
}








public function handle(Request $request, $next)
{
if ($this->shouldSpecifyTrustedHosts()) {
Request::setTrustedHosts(array_filter($this->hosts()));
}

return $next($request);
}








public static function at(array|callable $hosts, bool $subdomains = true)
{
static::$alwaysTrust = $hosts;
static::$subdomains = $subdomains;
}






protected function shouldSpecifyTrustedHosts()
{
return ! $this->app->environment('local') &&
! $this->app->runningUnitTests();
}






protected function allSubdomainsOfApplicationUrl()
{
if ($host = parse_url($this->app['config']->get('app.url'), PHP_URL_HOST)) {
return '^(.+\.)?'.preg_quote($host).'$';
}
}






public static function flushState()
{
static::$alwaysTrust = null;
static::$subdomains = null;
}
}
