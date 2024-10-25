<?php









namespace Mockery\Generator\StringManipulation\Pass;

use Mockery\Generator\MockConfiguration;
use function str_replace;

class CallTypeHintPass implements Pass
{




public function apply($code, MockConfiguration $config)
{
if ($config->requiresCallTypeHintRemoval()) {
$code = str_replace(
'public function __call($method, array $args)',
'public function __call($method, $args)',
$code
);
}

if ($config->requiresCallStaticTypeHintRemoval()) {
return str_replace(
'public static function __callStatic($method, array $args)',
'public static function __callStatic($method, $args)',
$code
);
}

return $code;
}
}
