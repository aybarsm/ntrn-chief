<?php declare(strict_types=1);










namespace Monolog\Processor;






abstract class MemoryProcessor implements ProcessorInterface
{



protected bool $realUsage;




protected bool $useFormatting;





public function __construct(bool $realUsage = true, bool $useFormatting = true)
{
$this->realUsage = $realUsage;
$this->useFormatting = $useFormatting;
}






protected function formatBytes(int $bytes)
{
if (!$this->useFormatting) {
return $bytes;
}

if ($bytes > 1024 * 1024) {
return round($bytes / 1024 / 1024, 2).' MB';
} elseif ($bytes > 1024) {
return round($bytes / 1024, 2).' KB';
}

return $bytes . ' B';
}
}
