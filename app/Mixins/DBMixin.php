<?php

namespace App\Mixins;

use Illuminate\Support\Facades\Context;

/** @mixin \Illuminate\Database\DatabaseManager */
class DBMixin
{
    const string BIND = \Illuminate\Database\DatabaseManager::class;

    public static function timestampAwareTransactions(): \Closure
    {
        return function (bool $enabled = true): void {
            Context::add('app.db.timestampAwareTransactions', $enabled);
        };
    }

    public static function isTimestampAwareTransactions(): \Closure
    {
        return function (): bool {
            return Context::get('app.db.timestampAwareTransactions', false);
        };
    }
}
