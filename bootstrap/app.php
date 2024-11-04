<?php

// TODO: Load env file to an array for dynamic config path etc...
use App\Framework\Application;

$app = Application::configure(basePath: dirname(__DIR__))->create();

if (\Phar::running(false)) {
    if (! blank(getenv('NTRN_BASE')) && file_exists(getenv('NTRN_BASE')) && is_dir(getenv('NTRN_BASE'))) {
        $app->useEnvironmentPath(getenv('NTRN_BASE'));
        $app->useStoragePath(getenv('NTRN_BASE'));
    }
} elseif (file_exists(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev') && is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev')) {
    $app->useEnvironmentPath(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev');
    $app->useStoragePath(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev');
}

return $app;
