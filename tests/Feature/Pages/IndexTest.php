<?php

test('the page can be rendered', function () {

    $response = $this->get(route('index'));

    $response->assertStatus(200);
    $response->assertViewIs('index');
    $response->assertSeeText('YouTube Channel Notifier');
});
