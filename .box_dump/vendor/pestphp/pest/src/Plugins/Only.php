<?php

declare(strict_types=1);

namespace Pest\Plugins;

use Pest\Contracts\Plugins\Terminable;
use Pest\PendingCalls\TestCall;




final class Only implements Terminable
{



private const TEMPORARY_FOLDER = __DIR__
.DIRECTORY_SEPARATOR
.'..'
.DIRECTORY_SEPARATOR
.'..'
.DIRECTORY_SEPARATOR
.'.temp';




public function terminate(): void
{
$lockFile = self::TEMPORARY_FOLDER.DIRECTORY_SEPARATOR.'only.lock';

if (file_exists($lockFile)) {
unlink($lockFile);
}
}




public static function enable(TestCall $testCall): void
{
if (Environment::name() == Environment::CI) {
return;
}

$testCall->group('__pest_only');

$lockFile = self::TEMPORARY_FOLDER.DIRECTORY_SEPARATOR.'only.lock';

if (! file_exists($lockFile)) {
touch($lockFile);
}
}




public static function isEnabled(): bool
{
$lockFile = self::TEMPORARY_FOLDER.DIRECTORY_SEPARATOR.'only.lock';

return file_exists($lockFile);
}
}
