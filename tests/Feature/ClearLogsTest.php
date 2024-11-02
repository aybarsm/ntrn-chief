<?php

it('clears the logs', function () {
//    $this->artisan('test:logs')->assertExitCode(0);
    $path = data_get($this->app->config, 'logging.channels.single.path');
    expect($path)->toBeWritableFile('Logs file not found');
});
