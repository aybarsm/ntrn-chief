<?php

namespace Faker\Provider;

use Faker\Calculator\Ean;
use Faker\Calculator\Isbn;





class Barcode extends Base
{
private function ean($length = 13)
{
$code = static::numerify(str_repeat('#', $length - 1));

return $code . Ean::checksum($code);
}










protected static function eanChecksum($input)
{
return Ean::checksum($input);
}













protected static function isbnChecksum($input)
{
return Isbn::checksum($input);
}








public function ean13()
{
return $this->ean(13);
}








public function ean8()
{
return $this->ean(8);
}










public function isbn10()
{
$code = static::numerify(str_repeat('#', 9));

return $code . Isbn::checksum($code);
}










public function isbn13()
{
$code = '97' . self::numberBetween(8, 9) . static::numerify(str_repeat('#', 9));

return $code . Ean::checksum($code);
}
}
