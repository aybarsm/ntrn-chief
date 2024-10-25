<?php declare(strict_types=1);








namespace SebastianBergmann\LinesOfCode;

/**
@psalm-immutable
*/
final class LinesOfCode
{
/**
@psalm-var
*/
private readonly int $linesOfCode;

/**
@psalm-var
*/
private readonly int $commentLinesOfCode;

/**
@psalm-var
*/
private readonly int $nonCommentLinesOfCode;

/**
@psalm-var
*/
private readonly int $logicalLinesOfCode;

/**
@psalm-param
@psalm-param
@psalm-param
@psalm-param



*/
public function __construct(int $linesOfCode, int $commentLinesOfCode, int $nonCommentLinesOfCode, int $logicalLinesOfCode)
{
/**
@psalm-suppress */
if ($linesOfCode < 0) {
throw new NegativeValueException('$linesOfCode must not be negative');
}

/**
@psalm-suppress */
if ($commentLinesOfCode < 0) {
throw new NegativeValueException('$commentLinesOfCode must not be negative');
}

/**
@psalm-suppress */
if ($nonCommentLinesOfCode < 0) {
throw new NegativeValueException('$nonCommentLinesOfCode must not be negative');
}

/**
@psalm-suppress */
if ($logicalLinesOfCode < 0) {
throw new NegativeValueException('$logicalLinesOfCode must not be negative');
}

if ($linesOfCode - $commentLinesOfCode !== $nonCommentLinesOfCode) {
throw new IllogicalValuesException('$linesOfCode !== $commentLinesOfCode + $nonCommentLinesOfCode');
}

$this->linesOfCode = $linesOfCode;
$this->commentLinesOfCode = $commentLinesOfCode;
$this->nonCommentLinesOfCode = $nonCommentLinesOfCode;
$this->logicalLinesOfCode = $logicalLinesOfCode;
}

/**
@psalm-return
*/
public function linesOfCode(): int
{
return $this->linesOfCode;
}

/**
@psalm-return
*/
public function commentLinesOfCode(): int
{
return $this->commentLinesOfCode;
}

/**
@psalm-return
*/
public function nonCommentLinesOfCode(): int
{
return $this->nonCommentLinesOfCode;
}

/**
@psalm-return
*/
public function logicalLinesOfCode(): int
{
return $this->logicalLinesOfCode;
}

public function plus(self $other): self
{
return new self(
$this->linesOfCode() + $other->linesOfCode(),
$this->commentLinesOfCode() + $other->commentLinesOfCode(),
$this->nonCommentLinesOfCode() + $other->nonCommentLinesOfCode(),
$this->logicalLinesOfCode() + $other->logicalLinesOfCode(),
);
}
}
