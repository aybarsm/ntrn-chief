<?php

namespace App\Enums;

enum RosConfig: string
{
    case NATIVE = 'native';
    case COMMANDS = 'commands';
    case JSON = 'json';
    case JSON_PRETTY = 'json-pretty';
    case ARRAY = 'array';

    case ARRAY_DOT = 'array_dot';
    case OBJECT = 'object';
    case LITERAL = 'literal';
    case FLUENT = 'fluent';
}
