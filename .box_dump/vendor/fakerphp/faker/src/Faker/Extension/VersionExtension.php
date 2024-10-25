<?php

namespace Faker\Extension;

/**
@experimental
*/
interface VersionExtension extends Extension
{










public function semver(bool $preRelease = false, bool $build = false): string;
}
