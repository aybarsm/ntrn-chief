<?php

declare(strict_types=1);










namespace NunoMaduro\LaravelConsoleMenu;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;




class LaravelConsoleMenuServiceProvider extends ServiceProvider
{



public function boot()
{








Command::macro(
'menu',
function (string $title = '', array $options = []) {
return new Menu($title, $options);
}
);
}
}
