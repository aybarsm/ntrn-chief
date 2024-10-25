<?php













namespace Assert;

use LogicException;


































































































class LazyAssertion
{
private $currentChainFailed = false;
private $alwaysTryAll = false;
private $thisChainTryAll = false;
private $currentChain;
private $errors = [];


private $assertClass = Assert::class;


private $exceptionClass = LazyAssertionException::class;







public function that($value, string $propertyPath = null, $defaultMessage = null)
{
$this->currentChainFailed = false;
$this->thisChainTryAll = false;
$assertClass = $this->assertClass;
$this->currentChain = $assertClass::that($value, $defaultMessage, $propertyPath);

return $this;
}




public function tryAll()
{
if (!$this->currentChain) {
$this->alwaysTryAll = true;
}

$this->thisChainTryAll = true;

return $this;
}







public function __call($method, $args)
{
if (false === $this->alwaysTryAll
&& false === $this->thisChainTryAll
&& true === $this->currentChainFailed
) {
return $this;
}

try {
\call_user_func_array([$this->currentChain, $method], $args);
} catch (AssertionFailedException $e) {
$this->errors[] = $e;
$this->currentChainFailed = true;
}

return $this;
}




public function verifyNow(): bool
{
if ($this->errors) {
throw \call_user_func([$this->exceptionClass, 'fromErrors'], $this->errors);
}

return true;
}






public function setAssertClass(string $className): LazyAssertion
{
if (Assert::class !== $className && !\is_subclass_of($className, Assert::class)) {
throw new LogicException($className.' is not (a subclass of) '.Assert::class);
}

$this->assertClass = $className;

return $this;
}






public function setExceptionClass(string $className): LazyAssertion
{
if (LazyAssertionException::class !== $className && !\is_subclass_of($className, LazyAssertionException::class)) {
throw new LogicException($className.' is not (a subclass of) '.LazyAssertionException::class);
}

$this->exceptionClass = $className;

return $this;
}
}
