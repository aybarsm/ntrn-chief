<?php

namespace Faker\Extension;

/**
@experimental
*/
interface FileExtension extends Extension
{





public function mimeType(): string;






public function extension(): string;




public function filePath(): string;
}
