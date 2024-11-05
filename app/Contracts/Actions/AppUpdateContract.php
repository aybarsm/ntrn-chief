<?php

namespace App\Contracts\Actions;

interface AppUpdateContract
{
    public function __invoke(string $appVer);
}
