<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Video Filtering Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration holds settings for filtering out unwanted videos.
    | Any video with a title containing these terms will be excluded
    | from import and notifications to reduce alert noise.
    |
    */

    'skip_terms' => [
        'live',
        'LIVE',
        'premiere',
        'trailer',
        'teaser',
        'preview',
    ],

];
