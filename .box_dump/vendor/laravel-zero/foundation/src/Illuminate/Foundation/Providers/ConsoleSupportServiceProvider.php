<?php

namespace Illuminate\Foundation\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

class ConsoleSupportServiceProvider extends AggregateServiceProvider implements DeferrableProvider
{





protected $providers = [
ArtisanServiceProvider::class,
MigrationServiceProvider::class,
ComposerServiceProvider::class,
];
}
