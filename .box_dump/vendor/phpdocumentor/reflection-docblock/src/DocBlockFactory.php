<?php

declare(strict_types=1);










namespace phpDocumentor\Reflection;

use InvalidArgumentException;
use LogicException;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\AbstractPHPStanFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\Factory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\MethodFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ParamFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyReadFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\PropertyWriteFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\ReturnFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\VarFactory;
use Webmozart\Assert\Assert;

use function array_shift;
use function count;
use function explode;
use function is_object;
use function method_exists;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strpos;
use function substr;
use function trim;

final class DocBlockFactory implements DocBlockFactoryInterface
{
private DocBlock\DescriptionFactory $descriptionFactory;

private TagFactory $tagFactory;




public function __construct(DescriptionFactory $descriptionFactory, TagFactory $tagFactory)
{
$this->descriptionFactory = $descriptionFactory;
$this->tagFactory = $tagFactory;
}






public static function createInstance(array $additionalTags = []): DocBlockFactoryInterface
{
$fqsenResolver = new FqsenResolver();
$tagFactory = new StandardTagFactory($fqsenResolver);
$descriptionFactory = new DescriptionFactory($tagFactory);
$typeResolver = new TypeResolver($fqsenResolver);

$phpstanTagFactory = new AbstractPHPStanFactory(
new ParamFactory($typeResolver, $descriptionFactory),
new VarFactory($typeResolver, $descriptionFactory),
new ReturnFactory($typeResolver, $descriptionFactory),
new PropertyFactory($typeResolver, $descriptionFactory),
new PropertyReadFactory($typeResolver, $descriptionFactory),
new PropertyWriteFactory($typeResolver, $descriptionFactory),
new MethodFactory($typeResolver, $descriptionFactory)
);

$tagFactory->addService($descriptionFactory);
$tagFactory->addService($typeResolver);
$tagFactory->registerTagHandler('param', $phpstanTagFactory);
$tagFactory->registerTagHandler('var', $phpstanTagFactory);
$tagFactory->registerTagHandler('return', $phpstanTagFactory);
$tagFactory->registerTagHandler('property', $phpstanTagFactory);
$tagFactory->registerTagHandler('property-read', $phpstanTagFactory);
$tagFactory->registerTagHandler('property-write', $phpstanTagFactory);
$tagFactory->registerTagHandler('method', $phpstanTagFactory);

$docBlockFactory = new self($descriptionFactory, $tagFactory);
foreach ($additionalTags as $tagName => $tagHandler) {
$docBlockFactory->registerTagHandler($tagName, $tagHandler);
}

return $docBlockFactory;
}





public function create($docblock, ?Types\Context $context = null, ?Location $location = null): DocBlock
{
if (is_object($docblock)) {
if (!method_exists($docblock, 'getDocComment')) {
$exceptionMessage = 'Invalid object passed; the given object must support the getDocComment method';

throw new InvalidArgumentException($exceptionMessage);
}

$docblock = $docblock->getDocComment();
Assert::string($docblock);
}

Assert::stringNotEmpty($docblock);

if ($context === null) {
$context = new Types\Context('');
}

$parts = $this->splitDocBlock($this->stripDocComment($docblock));

[$templateMarker, $summary, $description, $tags] = $parts;

return new DocBlock(
$summary,
$description ? $this->descriptionFactory->create($description, $context) : null,
$this->parseTagBlock($tags, $context),
$context,
$location,
$templateMarker === '#@+',
$templateMarker === '#@-'
);
}




public function registerTagHandler(string $tagName, $handler): void
{
$this->tagFactory->registerTagHandler($tagName, $handler);
}






private function stripDocComment(string $comment): string
{
$comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]?(.*)?#u', '$1', $comment);
Assert::string($comment);
$comment = trim($comment);


if (substr($comment, -2) === '*/') {
$comment = trim(substr($comment, 0, -2));
}

return str_replace(["\r\n", "\r"], "\n", $comment);
}














private function splitDocBlock(string $comment): array
{




if (strpos($comment, '@') === 0) {
return ['', '', '', $comment];
}


$comment = preg_replace('/\h*$/Sum', '', $comment);
Assert::string($comment);














preg_match(
'/
            \A
            # 1. Extract the template marker
            (?:(\#\@\+|\#\@\-)\n?)?

            # 2. Extract the summary
            (?:
              (?! @\pL ) # The summary may not start with an @
              (
                [^\n.]+
                (?:
                  (?! \. \n | \n{2} )     # End summary upon a dot followed by newline or two newlines
                  [\n.]* (?! [ \t]* @\pL ) # End summary when an @ is found as first character on a new line
                  [^\n.]+                 # Include anything else
                )*
                \.?
              )?
            )

            # 3. Extract the description
            (?:
              \s*        # Some form of whitespace _must_ precede a description because a summary must be there
              (?! @\pL ) # The description may not start with an @
              (
                [^\n]+
                (?: \n+
                  (?! [ \t]* @\pL ) # End description when an @ is found as first character on a new line
                  [^\n]+            # Include anything else
                )*
              )
            )?

            # 4. Extract the tags (anything that follows)
            (\s+ [\s\S]*)? # everything that follows
            /ux',
$comment,
$matches
);
array_shift($matches);

while (count($matches) < 4) {
$matches[] = '';
}

return $matches;
}









private function parseTagBlock(string $tags, Types\Context $context): array
{
$tags = $this->filterTagBlock($tags);
if ($tags === null) {
return [];
}

$result = [];
$lines = $this->splitTagBlockIntoTagLines($tags);
foreach ($lines as $key => $tagLine) {
$result[$key] = $this->tagFactory->create(trim($tagLine), $context);
}

return $result;
}




private function splitTagBlockIntoTagLines(string $tags): array
{
$result = [];
foreach (explode("\n", $tags) as $tagLine) {
if ($tagLine !== '' && strpos($tagLine, '@') === 0) {
$result[] = $tagLine;
} else {
$result[count($result) - 1] .= "\n" . $tagLine;
}
}

return $result;
}

private function filterTagBlock(string $tags): ?string
{
$tags = trim($tags);
if (!$tags) {
return null;
}

if ($tags[0] !== '@') {




throw new LogicException('A tag block started with text instead of an at-sign(@): ' . $tags);


}

return $tags;
}
}
