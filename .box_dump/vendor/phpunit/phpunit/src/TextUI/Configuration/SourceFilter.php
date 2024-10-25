<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments


*/
final class SourceFilter
{
public function includes(Source $source, string $path): bool
{
$files = (new SourceMapper)->map($source);

return isset($files[$path]);
}
}
