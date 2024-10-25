<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\StaticAnalysis;

/**
@psalm-import-type
@psalm-import-type
@psalm-import-type
@psalm-import-type
@psalm-import-type
@psalm-import-type
@psalm-type
@psalm-type







*/
interface FileAnalyser
{
/**
@psalm-return
*/
public function classesIn(string $filename): array;

/**
@psalm-return
*/
public function traitsIn(string $filename): array;

/**
@psalm-return
*/
public function functionsIn(string $filename): array;

/**
@psalm-return
*/
public function linesOfCodeFor(string $filename): array;

/**
@psalm-return
*/
public function executableLinesIn(string $filename): array;

/**
@psalm-return
*/
public function ignoredLinesFor(string $filename): array;
}
