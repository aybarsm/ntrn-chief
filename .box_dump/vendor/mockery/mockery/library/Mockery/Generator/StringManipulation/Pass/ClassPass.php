<?php









namespace Mockery\Generator\StringManipulation\Pass;

use Mockery;
use Mockery\Generator\MockConfiguration;
use function class_exists;
use function ltrim;
use function str_replace;

class ClassPass implements Pass
{




public function apply($code, MockConfiguration $config)
{
$target = $config->getTargetClass();

if (! $target) {
return $code;
}

if ($target->isFinal()) {
return $code;
}

$className = ltrim($target->getName(), '\\');

if (! class_exists($className)) {
Mockery::declareClass($className);
}

return str_replace(
'implements MockInterface',
'extends \\' . $className . ' implements MockInterface',
$code
);
}
}
