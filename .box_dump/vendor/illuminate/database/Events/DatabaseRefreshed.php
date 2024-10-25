<?php

namespace Illuminate\Database\Events;

use Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;

class DatabaseRefreshed implements MigrationEventContract
{







public function __construct(
public ?string $database = null,
public bool $seeding = false
) {

}
}
