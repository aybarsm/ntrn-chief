<?php

declare(strict_types=1);










namespace phpDocumentor\Reflection\DocBlock\Tags;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use phpDocumentor\Reflection\Utils;
use Webmozart\Assert\Assert;

use function array_shift;
use function array_unshift;
use function implode;
use function strpos;
use function substr;

use const PREG_SPLIT_DELIM_CAPTURE;




final class Param extends TagWithType implements Factory\StaticMethod
{
private ?string $variableName = null;


private bool $isVariadic;


private bool $isReference;

public function __construct(
?string $variableName,
?Type $type = null,
bool $isVariadic = false,
?Description $description = null,
bool $isReference = false
) {
$this->name = 'param';
$this->variableName = $variableName;
$this->type = $type;
$this->isVariadic = $isVariadic;
$this->description = $description;
$this->isReference = $isReference;
}





public static function create(
string $body,
?TypeResolver $typeResolver = null,
?DescriptionFactory $descriptionFactory = null,
?TypeContext $context = null
): self {
Deprecation::triggerIfCalledFromOutside(
'phpdocumentor/reflection-docblock',
'https://github.com/phpDocumentor/ReflectionDocBlock/issues/361',
'Create using static factory is deprecated, this method should not be called directly
             by library consumers',
);

Assert::stringNotEmpty($body);
Assert::notNull($typeResolver);
Assert::notNull($descriptionFactory);

[$firstPart, $body] = self::extractTypeFromBody($body);

$type = null;
$parts = Utils::pregSplit('/(\s+)/Su', $body, 2, PREG_SPLIT_DELIM_CAPTURE);
$variableName = '';
$isVariadic = false;
$isReference = false;


if ($firstPart && !self::strStartsWithVariable($firstPart)) {
$type = $typeResolver->resolve($firstPart, $context);
} else {

array_unshift($parts, $firstPart);
}


if (isset($parts[0]) && self::strStartsWithVariable($parts[0])) {
$variableName = array_shift($parts);
if ($type) {
array_shift($parts);
}

Assert::notNull($variableName);

if (strpos($variableName, '$') === 0) {
$variableName = substr($variableName, 1);
} elseif (strpos($variableName, '&$') === 0) {
$isReference = true;
$variableName = substr($variableName, 2);
} elseif (strpos($variableName, '...$') === 0) {
$isVariadic = true;
$variableName = substr($variableName, 4);
} elseif (strpos($variableName, '&...$') === 0) {
$isVariadic = true;
$isReference = true;
$variableName = substr($variableName, 5);
}
}

$description = $descriptionFactory->create(implode('', $parts), $context);

return new static($variableName, $type, $isVariadic, $description, $isReference);
}




public function getVariableName(): ?string
{
return $this->variableName;
}




public function isVariadic(): bool
{
return $this->isVariadic;
}




public function isReference(): bool
{
return $this->isReference;
}




public function __toString(): string
{
if ($this->description) {
$description = $this->description->render();
} else {
$description = '';
}

$variableName = '';
if ($this->variableName) {
$variableName .= ($this->isReference ? '&' : '') . ($this->isVariadic ? '...' : '');
$variableName .= '$' . $this->variableName;
}

$type = (string) $this->type;

return $type
. ($variableName !== '' ? ($type !== '' ? ' ' : '') . $variableName : '')
. ($description !== '' ? ($type !== '' || $variableName !== '' ? ' ' : '') . $description : '');
}

private static function strStartsWithVariable(string $str): bool
{
return strpos($str, '$') === 0
||
strpos($str, '...$') === 0
||
strpos($str, '&$') === 0
||
strpos($str, '&...$') === 0;
}
}
