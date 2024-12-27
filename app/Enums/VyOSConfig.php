<?php

namespace App\Enums;

enum VyOSConfig: string
{
    case NATIVE = 'native';
    case COMMANDS = 'commands';
    case JSON = 'json';
    case JSON_PRETTY = 'json-pretty';
    case ARRAY = 'array';
    case OBJECT = 'object';
    case LITERAL = 'literal';
    case FLUENT = 'fluent';
}
