<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $word
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'word',
])]
class ExcludedWord extends Model
{
    /**
     * @return array<string>
     */
    public static function getWords(): array
    {
        return static::pluck('word')->toArray();
    }
}
