<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

/**
@no-named-arguments


*/
final class RemoveCoverageElementProcessUncoveredFilesAttribute implements Migration
{
public function migrate(DOMDocument $document): void
{
$node = $document->getElementsByTagName('coverage')->item(0);

if (!$node instanceof DOMElement || $node->parentNode === null) {
return;
}

if ($node->hasAttribute('processUncoveredFiles')) {
$node->removeAttribute('processUncoveredFiles');
}
}
}
