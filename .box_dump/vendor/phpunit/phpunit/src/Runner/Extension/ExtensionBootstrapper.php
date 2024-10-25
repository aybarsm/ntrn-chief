<?php declare(strict_types=1);








namespace PHPUnit\Runner\Extension;

use const PHP_EOL;
use function assert;
use function class_exists;
use function class_implements;
use function in_array;
use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use Throwable;

/**
@no-named-arguments


*/
final class ExtensionBootstrapper
{
private readonly Configuration $configuration;
private readonly Facade $facade;

public function __construct(Configuration $configuration, Facade $facade)
{
$this->configuration = $configuration;
$this->facade = $facade;
}

/**
@psalm-param
@psalm-param
*/
public function bootstrap(string $className, array $parameters): void
{
if (!class_exists($className)) {
EventFacade::emitter()->testRunnerTriggeredWarning(
sprintf(
'Cannot bootstrap extension because class %s does not exist',
$className,
),
);

return;
}

if (!in_array(Extension::class, class_implements($className), true)) {
EventFacade::emitter()->testRunnerTriggeredWarning(
sprintf(
'Cannot bootstrap extension because class %s does not implement interface %s',
$className,
Extension::class,
),
);

return;
}

try {
$instance = (new ReflectionClass($className))->newInstance();

assert($instance instanceof Extension);

$instance->bootstrap(
$this->configuration,
$this->facade,
ParameterCollection::fromArray($parameters),
);
} catch (Throwable $t) {
EventFacade::emitter()->testRunnerTriggeredWarning(
sprintf(
'Bootstrapping of extension %s failed: %s%s%s',
$className,
$t->getMessage(),
PHP_EOL,
$t->getTraceAsString(),
),
);

return;
}

EventFacade::emitter()->testRunnerBootstrappedExtension(
$className,
$parameters,
);
}
}
