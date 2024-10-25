<?php

namespace Faker\Extension;

/**
@experimental
*/
interface CompanyExtension extends Extension
{



public function company(): string;




public function companySuffix(): string;

public function jobTitle(): string;
}
