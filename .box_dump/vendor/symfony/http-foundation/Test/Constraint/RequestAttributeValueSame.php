<?php










namespace Symfony\Component\HttpFoundation\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Request;

final class RequestAttributeValueSame extends Constraint
{
private string $name;
private string $value;

public function __construct(string $name, string $value)
{
$this->name = $name;
$this->value = $value;
}

public function toString(): string
{
return sprintf('has attribute "%s" with value "%s"', $this->name, $this->value);
}




protected function matches($request): bool
{
return $this->value === $request->attributes->get($this->name);
}




protected function failureDescription($request): string
{
return 'the Request '.$this->toString();
}
}
