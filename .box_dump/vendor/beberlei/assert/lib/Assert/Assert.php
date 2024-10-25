<?php













namespace Assert;




abstract class Assert
{

protected static $lazyAssertionExceptionClass = LazyAssertionException::class;


protected static $assertionClass = Assertion::class;


















public static function that($value, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
$assertionChain = new AssertionChain($value, $defaultMessage, $defaultPropertyPath);

return $assertionChain->setAssertionClassName(static::$assertionClass);
}







public static function thatAll($values, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
return static::that($values, $defaultMessage, $defaultPropertyPath)->all();
}







public static function thatNullOr($value, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
return static::that($value, $defaultMessage, $defaultPropertyPath)->nullOr();
}




public static function lazy(): LazyAssertion
{
$lazyAssertion = new LazyAssertion();

return $lazyAssertion
->setAssertClass(\get_called_class())
->setExceptionClass(static::$lazyAssertionExceptionClass);
}
}
