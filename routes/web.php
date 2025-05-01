<?php

declare(strict_types=1);

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Route;

Route::view('/', 'index')->name('index');

Route::get('/mail', function () {
    if (! Config::get('app.debug')) {
        abort(404);
    }

    $channel = Channel::factory()->create();
    $video = Video::factory()->create([
        'channel_id' => $channel->id,
    ]);

    return new NewVideoMail($video, $channel)->render();
});
