<?php

declare(strict_types=1);










namespace Carbon\Traits;

use Carbon\Exceptions\InvalidCastException;
use DateTimeInterface;






trait Cast
{
/**
@template






*/
public function cast(string $className): mixed
{
if (!method_exists($className, 'instance')) {
if (is_a($className, DateTimeInterface::class, true)) {
return new $className($this->rawFormat('Y-m-d H:i:s.u'), $this->getTimezone());
}

throw new InvalidCastException("$className has not the instance() method needed to cast the date.");
}

return $className::instance($this);
}
}
