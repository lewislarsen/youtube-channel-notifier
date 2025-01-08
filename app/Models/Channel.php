<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get the videos associated with the channel.
     *
     * @return HasMany The relationship instance between the channel and its videos.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
