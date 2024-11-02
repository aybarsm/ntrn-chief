<?php

it('builds the app and distributions', function () {
    $this->artisan('app:build')->assertExitCode(0);
});
