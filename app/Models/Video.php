<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    public $guarded = [];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
