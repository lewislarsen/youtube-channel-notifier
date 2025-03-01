<?php

use Illuminate\Support\Facades\Config;

it('prevents access to mail route when debug mode is off', function () {
    Config::set('app.debug', false);

    $this->get('/mail')->assertNotFound();
});

it('allows access to mail route when debug mode is on', function () {
    Config::set('app.debug', true);

    $this->get('/mail')->assertSuccessful();
});
