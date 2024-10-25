<?php

namespace Faker\Extension;

/**
@experimental
*/
interface PersonExtension extends Extension
{
public const GENDER_FEMALE = 'female';
public const GENDER_MALE = 'male';






public function name(?string $gender = null): string;






public function firstName(?string $gender = null): string;

public function firstNameMale(): string;

public function firstNameFemale(): string;




public function lastName(): string;






public function title(?string $gender = null): string;




public function titleMale(): string;




public function titleFemale(): string;
}
