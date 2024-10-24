<?php

use LaravelZero\Framework\Application;

$app = Application::configure(basePath: dirname(__DIR__))->create();

if (! blank(getenv('NTRN_BASE')) && file_exists(getenv('NTRN_BASE')) && is_dir(getenv('NTRN_BASE'))){
    $app->useEnvironmentPath(getenv('NTRN_BASE'));
    $app->useStoragePath(getenv('NTRN_BASE'));
}

return $app;
