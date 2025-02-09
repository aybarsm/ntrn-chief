<?php

// TODO: Load env file to an array for dynamic config path etc...
use App\Framework\Application;

$app = Application::configure(basePath: dirname(__DIR__))->create();

if (\Phar::running(false)) {
    if (getenv('NTRN_BASE') !== false && is_string(getenv('NTRN_BASE')) && ! blank(getenv('NTRN_BASE'))) {
        if (! file_exists(getenv('NTRN_BASE')) && truthy(getenv('NTRN_BASE_INIT'))) {
            mkdir(directory: getenv('NTRN_BASE'), recursive: true);
        }
        if (file_exists(getenv('NTRN_BASE')) && is_dir(getenv('NTRN_BASE'))) {
            $app->useEnvironmentPath(getenv('NTRN_BASE'));
            $app->useStoragePath(getenv('NTRN_BASE'));
        }
    } elseif (get_cfg_var('NTRN_BASE') !== false && is_string(get_cfg_var('NTRN_BASE')) && ! blank(get_cfg_var('NTRN_BASE'))) {
        if (! file_exists(get_cfg_var('NTRN_BASE')) && truthy(get_cfg_var('NTRN_BASE_INIT'))) {
            mkdir(directory: get_cfg_var('NTRN_BASE'), recursive: true);
        }
        if (file_exists(get_cfg_var('NTRN_BASE')) && is_dir(get_cfg_var('NTRN_BASE'))) {
            $app->useEnvironmentPath(get_cfg_var('NTRN_BASE'));
            $app->useStoragePath(get_cfg_var('NTRN_BASE'));
        }
    }
} elseif (file_exists(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev') && is_dir(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev')) {
    $app->useEnvironmentPath(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev');
    $app->useStoragePath(dirname(__DIR__).DIRECTORY_SEPARATOR.'dev');
}

return $app;
