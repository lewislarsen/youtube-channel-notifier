<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $word
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExcludedWord extends Model
{
    protected $fillable = [
        'word',
    ];

    /**
     * @return array<string>
     */
    public static function getWords(): array
    {
        return static::pluck('word')->toArray();
    }
}
