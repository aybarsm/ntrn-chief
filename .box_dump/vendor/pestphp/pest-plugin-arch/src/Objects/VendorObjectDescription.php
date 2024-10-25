<?php

declare(strict_types=1);

namespace Pest\Arch\Objects;

use Error;
use PHPUnit\Architecture\Elements\ObjectDescription;




final class VendorObjectDescription extends ObjectDescription 
{



public static function make(string $path): ?self 
{
$object = new self();

try {
$vendorObject = ObjectDescriptionBase::make($path);
} catch (Error) {
return null;
}

if (! $vendorObject instanceof ObjectDescriptionBase) {
return null;
}

$object->name = $vendorObject->name;

return $object;
}
}
