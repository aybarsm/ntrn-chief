<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use Ramsey\Uuid\Fields\FieldsInterface as BaseFieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;

/**
@psalm-immutable

















*/
interface FieldsInterface extends BaseFieldsInterface
{




public function getClockSeq(): Hexadecimal;




public function getClockSeqHiAndReserved(): Hexadecimal;




public function getClockSeqLow(): Hexadecimal;




public function getNode(): Hexadecimal;




public function getTimeHiAndVersion(): Hexadecimal;




public function getTimeLow(): Hexadecimal;




public function getTimeMid(): Hexadecimal;




public function getTimestamp(): Hexadecimal;
















public function getVariant(): int;




















public function getVersion(): ?int;







public function isNil(): bool;
}
