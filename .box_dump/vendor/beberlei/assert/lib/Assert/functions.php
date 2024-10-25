<?php













namespace Assert;



















function that($value, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
return Assert::that($value, $defaultMessage, $defaultPropertyPath);
}








function thatAll($values, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
return Assert::thatAll($values, $defaultMessage, $defaultPropertyPath);
}










function thatNullOr($value, $defaultMessage = null, string $defaultPropertyPath = null): AssertionChain
{
return Assert::thatNullOr($value, $defaultMessage, $defaultPropertyPath);
}




function lazy(): LazyAssertion
{
return Assert::lazy();
}
