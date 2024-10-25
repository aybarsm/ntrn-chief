<?php

namespace Faker\Extension;

/**
@experimental
*/
interface AddressExtension extends Extension
{



public function address(): string;




public function city(): string;




public function postcode(): string;




public function streetName(): string;




public function streetAddress(): string;




public function buildingNumber(): string;
}
