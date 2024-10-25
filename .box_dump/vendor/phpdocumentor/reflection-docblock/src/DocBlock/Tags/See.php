<?php

declare(strict_types=1);










namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use phpDocumentor\Reflection\Utils;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function explode;
use function preg_match;




final class See extends BaseTag implements Factory\StaticMethod
{
protected string $name = 'see';

protected Reference $refers;




public function __construct(Reference $refers, ?Description $description = null)
{
$this->refers = $refers;
$this->description = $description;
}

public static function create(
string $body,
?FqsenResolver $typeResolver = null,
?DescriptionFactory $descriptionFactory = null,
?TypeContext $context = null
): self {
Assert::notNull($descriptionFactory);

$parts = Utils::pregSplit('/\s+/Su', $body, 2);
$description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;


if (preg_match('#\w://\w#', $parts[0])) {
return new static(new Url($parts[0]), $description);
}

return new static(new FqsenRef(self::resolveFqsen($parts[0], $typeResolver, $context)), $description);
}

private static function resolveFqsen(string $parts, ?FqsenResolver $fqsenResolver, ?TypeContext $context): Fqsen
{
Assert::notNull($fqsenResolver);
$fqsenParts = explode('::', $parts);
$resolved = $fqsenResolver->resolve($fqsenParts[0], $context);

if (!array_key_exists(1, $fqsenParts)) {
return $resolved;
}

return new Fqsen($resolved . '::' . $fqsenParts[1]);
}




public function getReference(): Reference
{
return $this->refers;
}




public function __toString(): string
{
if ($this->description) {
$description = $this->description->render();
} else {
$description = '';
}

$refers = (string) $this->refers;

return $refers . ($description !== '' ? ($refers !== '' ? ' ' : '') . $description : '');
}
}