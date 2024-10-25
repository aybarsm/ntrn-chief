<?php










namespace Symfony\Component\HttpKernel\Attribute;

use Psr\Log\LogLevel;






#[\Attribute(\Attribute::TARGET_CLASS)]
final class WithLogLevel
{



public function __construct(public readonly string $level)
{
if (!\defined('Psr\Log\LogLevel::'.strtoupper($this->level))) {
throw new \InvalidArgumentException(sprintf('Invalid log level "%s".', $this->level));
}
}
}
