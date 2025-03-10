<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

it('prevents access to mail route when debug mode is off', function (): void {
    Config::set('app.debug', false);

    $this->get('/mail')->assertNotFound();
});

it('allows access to mail route when debug mode is on', function (): void {
    Config::set('app.debug', true);

    $this->get('/mail')->assertSuccessful();
});
