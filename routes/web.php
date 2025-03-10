<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('index');

Route::get('/mail', function () {
    if (! Config::get('app.debug')) {
        abort(404);
    }

    $channel = \App\Models\Channel::factory()->create();
    $video = \App\Models\Video::factory()->create([
        'channel_id' => $channel->id,
    ]);

    return (new \App\Mail\NewVideoMail($video, $channel))->render();
});
