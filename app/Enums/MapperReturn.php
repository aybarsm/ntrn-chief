<?php

namespace App\Enums;

enum MapperReturn: string
{
    case KEY = 'key';
    case VALUE = 'value';
    case KEY_REPLACE = 'key_replace';
    case VALUE_REPLACE = 'value_replace';
}
