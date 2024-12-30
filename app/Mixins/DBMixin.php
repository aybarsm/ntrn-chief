<?php

namespace App\Mixins;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;

/** @mixin \Illuminate\Database\DatabaseManager */
class DBMixin
{
    const string BIND = \Illuminate\Database\DatabaseManager::class;

    public static function hasColumn(): \Closure
    {
        return function (string $table, string|array $columns, bool $hasAll = true): bool
        {
            $columns = Arr::wrap($columns);
            $schemaColumns = static::getSchemaBuilder()->getColumnListing($table);
            $intersection = array_intersect($columns, $schemaColumns);
            return $hasAll ? count($intersection) === count($columns) : count($intersection) > 0;
        };
    }

    public static function timestampAwareTransactions(): \Closure
    {
        return function (bool $enabled = true): void
        {
            Context::add('app.db.timestampAwareTransactions', $enabled);
        };
    }

    public static function isTimestampAwareTransactions(): \Closure
    {
        return function (): bool
        {
            return Context::get('app.db.timestampAwareTransactions', false);
        };
    }
}
