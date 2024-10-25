<?php

declare(strict_types=1);

namespace phpDocumentor\Reflection\DocBlock\Tags\Factory;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Webmozart\Assert\Assert;

use function is_string;
use function trim;




final class PropertyReadFactory implements PHPStanFactory
{
private DescriptionFactory $descriptionFactory;
private TypeResolver $typeResolver;

public function __construct(TypeResolver $typeResolver, DescriptionFactory $descriptionFactory)
{
$this->typeResolver = $typeResolver;
$this->descriptionFactory = $descriptionFactory;
}

public function create(PhpDocTagNode $node, Context $context): Tag
{
$tagValue = $node->value;
Assert::isInstanceOf($tagValue, PropertyTagValueNode::class);

$description = $tagValue->getAttribute('description');
if (is_string($description) === false) {
$description = $tagValue->description;
}

return new PropertyRead(
trim($tagValue->propertyName, '$'),
$this->typeResolver->createType($tagValue->type, $context),
$this->descriptionFactory->create($description, $context)
);
}

public function supports(PhpDocTagNode $node, Context $context): bool
{
return $node->value instanceof PropertyTagValueNode && $node->name === '@property-read';
}
}