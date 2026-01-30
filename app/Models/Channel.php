<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\YouTube\ExtractYouTubeChannelAvatar;
use Database\Factories\ChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

/**
 * Class Channel
 *
 * This model represents a YouTube channel. It contains details about the channel
 * and establishes a relationship with the videos that belong to the channel.
 */
class Channel extends Model
{
    /** @use HasFactory<ChannelFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    public $guarded = [];

    /**
     * The attributes that have been cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'muted_at' => 'datetime',
        'published_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /**
     * Get the videos associated with the channel.
     *
     * @return HasMany<Video, $this> The relationship instance between the channel and its videos.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Update the last checked timestamp for the channel.
     */
    public function updateLastChecked(): void
    {
        $this->update(['last_checked_at' => \Illuminate\Support\Facades\Date::now()]);
        Log::debug("Check for videos completed for channel: {$this->name}.");
    }

    /**
     * Get the YouTube URL for this channel.
     */
    public function getChannelUrl(): string
    {
        return "https://www.youtube.com/channel/{$this->channel_id}";
    }

    /**
     * Determine if the channel is currently muted.
     */
    public function isMuted(): bool
    {
        return $this->muted_at !== null;
    }

    /**
     * Toggle the mute status of the channel.
     */
    public function toggleMute(): void
    {
        $this->muted_at = $this->isMuted() ? null : \Illuminate\Support\Facades\Date::now();
        $this->save();
    }

    /**
     * Dynamically fetches the Channel's avatar from YouTube.
     *
     * This is an HTTP request straight to YouTube and should be used sparingly.
     * We're not storing this as we want the live response.
     *
     * This functionality is likely to change in the future, I'm not 100% satisfied with it.
     * Perhaps cache it periodically to speed up querying this method? (using db cache driver)
     * Or we store it locally and then a command to "refresh" the URL from YT every day?
     * Ideally the fewest "moving parts" as possible, this is meant to be a light application.
     *
     * NOTE: as of 11/03/25 this is currently not being used anywhere.
     */
    public function getChannelAvatarUrl(): string
    {
        return (new ExtractYouTubeChannelAvatar)->execute($this);
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
        ];
    }
}
