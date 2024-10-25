<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration;

use function sprintf;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
@no-named-arguments


*/
abstract class LogToReportMigration implements Migration
{



public function migrate(DOMDocument $document): void
{
$coverage = $document->getElementsByTagName('coverage')->item(0);

if (!$coverage instanceof DOMElement) {
throw new MigrationException('Unexpected state - No coverage element');
}

$logNode = $this->findLogNode($document);

if ($logNode === null) {
return;
}

$reportChild = $this->toReportFormat($logNode);

$report = $coverage->getElementsByTagName('report')->item(0);

if ($report === null) {
$report = $coverage->appendChild($document->createElement('report'));
}

$report->appendChild($reportChild);
$logNode->parentNode->removeChild($logNode);
}

protected function migrateAttributes(DOMElement $src, DOMElement $dest, array $attributes): void
{
foreach ($attributes as $attr) {
if (!$src->hasAttribute($attr)) {
continue;
}

$dest->setAttribute($attr, $src->getAttribute($attr));
$src->removeAttribute($attr);
}
}

abstract protected function forType(): string;

abstract protected function toReportFormat(DOMElement $logNode): DOMElement;

private function findLogNode(DOMDocument $document): ?DOMElement
{
$logNode = (new DOMXPath($document))->query(
sprintf('//logging/log[@type="%s"]', $this->forType()),
)->item(0);

if (!$logNode instanceof DOMElement) {
return null;
}

return $logNode;
}
}
