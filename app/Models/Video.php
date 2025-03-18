<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Video
 *
 * This model represents a YouTube video. It contains details about the video
 * and establishes a relationship with the channel that the video belongs to.
 */
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    public $guarded = [];

    public $casts = ['published_at' => 'datetime'];

    /**
     * Get the channel that owns the video.
     *
     * @return BelongsTo<Channel, $this> The relationship instance between the video and its channel.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the YouTube URL for this video.
     */
    public function getYoutubeUrl(): string
    {
        return "https://www.youtube.com/watch?v={$this->video_id}";
    }

    /**
     * Get the thumbnail URL for this video.
     *
     * @param  string  $quality  The quality of the thumbnail ('default', 'hqdefault', 'mqdefault', 'sddefault', 'maxresdefault')
     */
    public function getThumbnailUrl(string $quality = 'hqdefault'): string
    {
        return "https://i.ytimg.com/vi/{$this->video_id}/{$quality}.jpg";
    }

    /**
     * Get the published date formatted for human-readable display.
     */
    public function getFormattedPublishedDate(): string
    {
        return $this->published_at->format('d M Y h:i A');
    }

    /**
     * Get the published date formatted for ISO8601 (used by Discord embeds).
     */
    public function getIsoPublishedDate(): string
    {
        return $this->published_at->toIso8601String();
    }
}
