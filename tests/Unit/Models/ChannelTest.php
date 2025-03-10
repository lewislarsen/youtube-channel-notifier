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

it('returns true if the channel is muted', function () {
    $channel = Channel::factory()->muted()->create();

    expect($channel->isMuted())->toBeTrue();
});

it('returns false if the channel is not muted', function () {
    $channel = Channel::factory()->unmuted()->create();

    expect($channel->isMuted())->toBeFalse();
});

it('can switch a channel to muted', function () {
    $channel = Channel::factory()->create(['muted_at' => null]);

    $channel->toggleMute();

    expect($channel->isMuted())->toBeTrue();
});

it('can unmute a muted channel', function () {
    $channel = Channel::factory()->muted()->create();

    $channel->toggleMute();

    expect($channel->isMuted())->toBeFalse();
});
