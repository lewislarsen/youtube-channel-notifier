<?php

use App\Models\Channel;

it('can update last checked value', function () {
    $channel = Channel::factory()->create(['last_checked_at' => null]);

    $channel->updateLastChecked();

    $this->assertNotNull($channel->last_checked_at);
});

it('returns the full youtube channel link', function () {
    $channelId = 'UCxyz123456';
    $channel = Channel::factory()->create(['channel_id' => $channelId]);

    // Verify the getChannelUrl method returns the correct URL
    expect($channel->getChannelUrl())->toBe("https://www.youtube.com/channel/{$channelId}");
});
