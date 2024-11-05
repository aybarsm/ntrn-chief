<?php

namespace App\Actions\AppUpdate;

use Illuminate\Container\Attributes\Config;

abstract class AppUpdateAction
{
    public function __invoke(
        #[Config('app.version')] string $appVer,

    )
    {

    }

}
