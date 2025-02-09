<?php

it('Initaite ', function () {
    $this->artisan('inspire')
        ->assertExitCode(0);
});
