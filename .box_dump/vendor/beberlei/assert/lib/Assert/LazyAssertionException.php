<?php













namespace Assert;

class LazyAssertionException extends InvalidArgumentException
{



private $errors = [];




public static function fromErrors(array $errors): self
{
$message = \sprintf('The following %d assertions failed:', \count($errors))."\n";

$i = 1;
foreach ($errors as $error) {
$message .= \sprintf("%d) %s: %s\n", $i++, $error->getPropertyPath(), $error->getMessage());
}

return new static($message, $errors);
}

public function __construct($message, array $errors)
{
parent::__construct($message, 0, null, null);

$this->errors = $errors;
}




public function getErrorExceptions(): array
{
return $this->errors;
}
}
