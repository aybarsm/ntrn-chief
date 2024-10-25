<?php

namespace Faker\Extension;

/**
@experimental
*/
interface PhoneNumberExtension extends Extension
{



public function phoneNumber(): string;




public function e164PhoneNumber(): string;
}
