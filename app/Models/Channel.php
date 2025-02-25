<?php

namespace App\Models;

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
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    /**
     * Get the videos associated with the channel.
     *
     * @return HasMany The relationship instance between the channel and its videos.
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
        $this->update(['last_checked_at' => now()]);
        Log::debug("Check for videos completed for channel: {$this->name}.");
    }

    /**
     * Get the YouTube URL for this channel.
     */
    public function getChannelUrl(): string
    {
        return "https://www.youtube.com/channel/{$this->channel_id}";
    }
}
