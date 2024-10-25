<?php











declare(strict_types=1);

namespace Ramsey\Uuid;




class BinaryUtils
{
/**
@psalm-pure









*/
public static function applyVariant(int $clockSeq): int
{
$clockSeq = $clockSeq & 0x3fff;
$clockSeq |= 0x8000;

return $clockSeq;
}

/**
@psalm-pure











*/
public static function applyVersion(int $timeHi, int $version): int
{
$timeHi = $timeHi & 0x0fff;
$timeHi |= $version << 12;

return $timeHi;
}
}
