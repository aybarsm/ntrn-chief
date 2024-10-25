<?php













namespace Assert;

use LogicException;
































































































class AssertionChain
{



private $value;




private $defaultMessage;




private $defaultPropertyPath;






private $alwaysValid = false;






private $all = false;


private $assertionClassName = 'Assert\Assertion';







public function __construct($value, $defaultMessage = null, string $defaultPropertyPath = null)
{
$this->value = $value;
$this->defaultMessage = $defaultMessage;
$this->defaultPropertyPath = $defaultPropertyPath;
}







public function __call($methodName, $args): AssertionChain
{
if (true === $this->alwaysValid) {
return $this;
}

try {
$method = new \ReflectionMethod($this->assertionClassName, $methodName);
} catch (\ReflectionException $exception) {
throw new \RuntimeException("Assertion '".$methodName."' does not exist.");
}

\array_unshift($args, $this->value);
$params = $method->getParameters();

foreach ($params as $idx => $param) {
if (isset($args[$idx])) {
continue;
}

switch ($param->getName()) {
case 'message':
$args[$idx] = $this->defaultMessage;
break;
case 'propertyPath':
$args[$idx] = $this->defaultPropertyPath;
break;
}
}

if ($this->all) {
$methodName = 'all'.$methodName;
}

\call_user_func_array([$this->assertionClassName, $methodName], $args);

return $this;
}




public function all(): AssertionChain
{
$this->all = true;

return $this;
}




public function nullOr(): AssertionChain
{
if (null === $this->value) {
$this->alwaysValid = true;
}

return $this;
}






public function setAssertionClassName($className): AssertionChain
{
if (!\is_string($className)) {
throw new LogicException('Exception class name must be passed as a string');
}

if (Assertion::class !== $className && !\is_subclass_of($className, Assertion::class)) {
throw new LogicException($className.' is not (a subclass of) '.Assertion::class);
}

$this->assertionClassName = $className;

return $this;
}
}
