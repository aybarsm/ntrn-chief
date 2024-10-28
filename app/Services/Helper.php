<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Helper
{
    public static function generateExtendedUlid(bool $md5 = false): string
    {
        $extended = (string)Str::ulid() . '|' . Carbon::now('UTC')->toIso8601ZuluString('microsecond');

        return $md5 ? md5($extended) : $extended;
    }

}
