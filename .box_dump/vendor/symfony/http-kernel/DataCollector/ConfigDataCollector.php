<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\VarDumper\Caster\ClassStub;
use Symfony\Component\VarDumper\Cloner\Data;






class ConfigDataCollector extends DataCollector implements LateDataCollectorInterface
{
private KernelInterface $kernel;




public function setKernel(KernelInterface $kernel): void
{
$this->kernel = $kernel;
}

public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
{
$eom = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_MAINTENANCE);
$eol = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_LIFE);

$this->data = [
'token' => $response->headers->get('X-Debug-Token'),
'symfony_version' => Kernel::VERSION,
'symfony_minor_version' => sprintf('%s.%s', Kernel::MAJOR_VERSION, Kernel::MINOR_VERSION),
'symfony_lts' => 4 === Kernel::MINOR_VERSION,
'symfony_state' => $this->determineSymfonyState(),
'symfony_eom' => $eom->format('F Y'),
'symfony_eol' => $eol->format('F Y'),
'env' => isset($this->kernel) ? $this->kernel->getEnvironment() : 'n/a',
'debug' => isset($this->kernel) ? $this->kernel->isDebug() : 'n/a',
'php_version' => \PHP_VERSION,
'php_architecture' => \PHP_INT_SIZE * 8,
'php_intl_locale' => class_exists(\Locale::class, false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a',
'php_timezone' => date_default_timezone_get(),
'xdebug_enabled' => \extension_loaded('xdebug'),
'apcu_enabled' => \extension_loaded('apcu') && filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOL),
'zend_opcache_enabled' => \extension_loaded('Zend OPcache') && filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOL),
'bundles' => [],
'sapi_name' => \PHP_SAPI,
];

if (isset($this->kernel)) {
foreach ($this->kernel->getBundles() as $name => $bundle) {
$this->data['bundles'][$name] = new ClassStub($bundle::class);
}
}

if (preg_match('~^(\d+(?:\.\d+)*)(.+)?$~', $this->data['php_version'], $matches) && isset($matches[2])) {
$this->data['php_version'] = $matches[1];
$this->data['php_version_extra'] = $matches[2];
}
}

public function lateCollect(): void
{
$this->data = $this->cloneVar($this->data);
}




public function getToken(): ?string
{
return $this->data['token'];
}




public function getSymfonyVersion(): string
{
return $this->data['symfony_version'];
}





public function getSymfonyState(): string
{
return $this->data['symfony_state'];
}





public function getSymfonyMinorVersion(): string
{
return $this->data['symfony_minor_version'];
}

public function isSymfonyLts(): bool
{
return $this->data['symfony_lts'];
}





public function getSymfonyEom(): string
{
return $this->data['symfony_eom'];
}





public function getSymfonyEol(): string
{
return $this->data['symfony_eol'];
}




public function getPhpVersion(): string
{
return $this->data['php_version'];
}




public function getPhpVersionExtra(): ?string
{
return $this->data['php_version_extra'] ?? null;
}

public function getPhpArchitecture(): int
{
return $this->data['php_architecture'];
}

public function getPhpIntlLocale(): string
{
return $this->data['php_intl_locale'];
}

public function getPhpTimezone(): string
{
return $this->data['php_timezone'];
}




public function getEnv(): string
{
return $this->data['env'];
}






public function isDebug(): bool|string
{
return $this->data['debug'];
}




public function hasXdebug(): bool
{
return $this->data['xdebug_enabled'];
}




public function hasXdebugInfo(): bool
{
return \function_exists('xdebug_info');
}




public function hasApcu(): bool
{
return $this->data['apcu_enabled'];
}




public function hasZendOpcache(): bool
{
return $this->data['zend_opcache_enabled'];
}

public function getBundles(): array|Data
{
return $this->data['bundles'];
}




public function getSapiName(): string
{
return $this->data['sapi_name'];
}

public function getName(): string
{
return 'config';
}

private function determineSymfonyState(): string
{
$now = new \DateTimeImmutable();
$eom = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_MAINTENANCE)->modify('last day of this month');
$eol = \DateTimeImmutable::createFromFormat('d/m/Y', '01/'.Kernel::END_OF_LIFE)->modify('last day of this month');

if ($now > $eol) {
$versionState = 'eol';
} elseif ($now > $eom) {
$versionState = 'eom';
} elseif ('' !== Kernel::EXTRA_VERSION) {
$versionState = 'dev';
} else {
$versionState = 'stable';
}

return $versionState;
}
}
