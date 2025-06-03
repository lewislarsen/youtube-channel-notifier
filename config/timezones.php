<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Common Timezones
    |--------------------------------------------------------------------------
    |
    | A curated list of commonly used timezones that users can choose from
    | during installation. These are used for emails and other notifications.
    | The list is designed to cover a wide range of geographical locations.
    | Please submit a PR if you would like to add more timezones.
    */

    'common' => [
        'UTC' => 'UTC (Coordinated Universal Time)',

        // US Timezones
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',

        // European Timezones
        'Europe/London' => 'London (GMT/BST)',
        'Europe/Amsterdam' => 'Amsterdam (CET/CEST)',
        'Europe/Berlin' => 'Berlin (CET/CEST)',
        'Europe/Copenhagen' => 'Copenhagen/Denmark (CET/CEST)',
        'Europe/Paris' => 'Paris (CET/CEST)',
        'Europe/Rome' => 'Rome (CET/CEST)',
        'Europe/Madrid' => 'Madrid (CET/CEST)',
        'Europe/Stockholm' => 'Stockholm (CET/CEST)',
        'Europe/Vienna' => 'Vienna (CET/CEST)',
        'Europe/Zurich' => 'Zurich (CET/CEST)',

        // Asia & Middle East
        'Asia/Tokyo' => 'Tokyo (JST)',
        'Asia/Hong_Kong' => 'Hong Kong (HKT)',
        'Asia/Shanghai' => 'Shanghai (CST)',
        'Asia/Seoul' => 'Seoul (KST)',
        'Asia/Singapore' => 'Singapore (SGT)',
        'Asia/Dubai' => 'Dubai (GST)',

        // Australia
        'Australia/Sydney' => 'Sydney (AEST/AEDT)',
        'Australia/Melbourne' => 'Melbourne (AEST/AEDT)',
        'Australia/Perth' => 'Perth (AWST)',
    ],

];
