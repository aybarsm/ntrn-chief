<?php










namespace Symfony\Component\HttpKernel\Exception;

class ResolverNotFoundException extends \RuntimeException
{



public function __construct(string $name, array $alternatives = [])
{
$msg = sprintf('You have requested a non-existent resolver "%s".', $name);
if ($alternatives) {
if (1 === \count($alternatives)) {
$msg .= ' Did you mean this: "';
} else {
$msg .= ' Did you mean one of these: "';
}
$msg .= implode('", "', $alternatives).'"?';
}

parent::__construct($msg);
}
}
