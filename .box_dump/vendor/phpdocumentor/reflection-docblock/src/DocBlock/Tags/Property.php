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




final class Property extends TagWithType implements Factory\StaticMethod
{
protected ?string $variableName = null;

public function __construct(?string $variableName, ?Type $type = null, ?Description $description = null)
{
Assert::string($variableName);

$this->name = 'property';
$this->variableName = $variableName;
$this->type = $type;
$this->description = $description;
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


if ($firstPart && $firstPart[0] !== '$') {
$type = $typeResolver->resolve($firstPart, $context);
} else {

array_unshift($parts, $firstPart);
}


if (isset($parts[0]) && strpos($parts[0], '$') === 0) {
$variableName = array_shift($parts);
if ($type) {
array_shift($parts);
}

Assert::notNull($variableName);

$variableName = substr($variableName, 1);
}

$description = $descriptionFactory->create(implode('', $parts), $context);

return new static($variableName, $type, $description);
}




public function getVariableName(): ?string
{
return $this->variableName;
}




public function __toString(): string
{
if ($this->description) {
$description = $this->description->render();
} else {
$description = '';
}

if ($this->variableName) {
$variableName = '$' . $this->variableName;
} else {
$variableName = '';
}

$type = (string) $this->type;

return $type
. ($variableName !== '' ? ($type !== '' ? ' ' : '') . $variableName : '')
. ($description !== '' ? ($type !== '' || $variableName !== '' ? ' ' : '') . $description : '');
}
}