<?php

it('builds the app and distributions', function () {
    $this->artisan('app:build')->assertExitCode(0);
})->skip('This test is skipped because it is not yet implemented.');
