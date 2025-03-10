<?php

declare(strict_types=1);

test('the page can be rendered', function (): void {

    $response = $this->get(route('index'));

    $response->assertStatus(200);
    $response->assertViewIs('index');
    $response->assertSeeText('YouTube Channel Notifier');
});
