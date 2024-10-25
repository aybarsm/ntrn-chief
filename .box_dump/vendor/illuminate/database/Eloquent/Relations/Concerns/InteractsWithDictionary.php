<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use InvalidArgumentException;
use UnitEnum;

use function Illuminate\Support\enum_value;

trait InteractsWithDictionary
{








protected function getDictionaryKey($attribute)
{
if (is_object($attribute)) {
if (method_exists($attribute, '__toString')) {
return $attribute->__toString();
}

if ($attribute instanceof UnitEnum) {
return enum_value($attribute);
}

throw new InvalidArgumentException('Model attribute value is an object but does not have a __toString method.');
}

return $attribute;
}
}
