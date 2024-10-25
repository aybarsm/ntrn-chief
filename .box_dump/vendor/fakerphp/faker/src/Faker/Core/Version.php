<?php

declare(strict_types=1);

namespace Faker\Core;

use Faker\Extension;
use Faker\Provider\DateTime;

/**
@experimental
*/
final class Version implements Extension\VersionExtension
{
private Extension\NumberExtension $numberExtension;



private array $semverCommonPreReleaseIdentifiers = ['alpha', 'beta', 'rc'];

public function __construct(Extension\NumberExtension $numberExtension = null)
{

$this->numberExtension = $numberExtension ?: new Number();
}




public function semver(bool $preRelease = false, bool $build = false): string
{
return sprintf(
'%d.%d.%d%s%s',
$this->numberExtension->numberBetween(0, 9),
$this->numberExtension->numberBetween(0, 99),
$this->numberExtension->numberBetween(0, 99),
$preRelease && $this->numberExtension->numberBetween(0, 1) === 1 ? '-' . $this->semverPreReleaseIdentifier() : '',
$build && $this->numberExtension->numberBetween(0, 1) === 1 ? '+' . $this->semverBuildIdentifier() : '',
);
}




private function semverPreReleaseIdentifier(): string
{
$ident = Extension\Helper::randomElement($this->semverCommonPreReleaseIdentifiers);

if ($this->numberExtension->numberBetween(0, 1) !== 1) {
return $ident;
}

return $ident . '.' . $this->numberExtension->numberBetween(1, 99);
}




private function semverBuildIdentifier(): string
{
if ($this->numberExtension->numberBetween(0, 1) === 1) {

return substr(sha1(Extension\Helper::lexify('??????')), 0, 7);
}


return DateTime::date('YmdHis');
}
}
