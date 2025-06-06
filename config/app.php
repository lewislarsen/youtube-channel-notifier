<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'YouTube Channel Notifier'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Emails
    |--------------------------------------------------------------------------
    |
    | This value is the email address(es) where alert notifications should be
    | sent. You can set this in your ".env" file to ensure that alerts are
    | sent to the appropriate recipient(s).
    |
    */

    'alert_emails' => array_map('trim', explode(',', (string) env('ALERT_EMAILS', 'alerts@example.com'))),

    /*
    |--------------------------------------------------------------------------
    | Discord Webhook URL
    |--------------------------------------------------------------------------
    |
    | This value is the webhook URL for sending notifications to Discord.
    | When configured, the application will send notifications to this
    | Discord channel in addition to other configured notification methods.
    |
    */

    'discord_webhook_url' => env('DISCORD_WEBHOOK_URL', null),

    /*
     * -------------------------------------------------------------------------
     * Webhook POST URL
     * -------------------------------------------------------------------------
     *
     * This value is the URL for the webhook that will be used to send
     * notifications to the specified endpoint.
     *
     * You can set this in your ".env" file to ensure that notifications
     * are sent to the appropriate endpoint.
     *
     * The URL should be a valid endpoint that can handle POST requests.
     *
     */
    'webhook_post_url' => env('WEBHOOK_POST_URL', null),

    /*
     * -------------------------------------------------------------------------
     * User Timezone
     * -------------------------------------------------------------------------
     *
     * This value determines the timezone used for displaying dates and times
     * to users throughout the application. This is separate from the application
     * timezone (APP_TIMEZONE) which is used for internal operations and database
     * storage. The user timezone affects how timestamps are presented in the UI,
     * notifications, and user-facing reports.
     *
     * You can set this in your ".env" file using USER_TIMEZONE.
     * Common values: 'America/New_York', 'Europe/London', 'Asia/Tokyo', etc.
     *
     */
    'user_timezone' => env('USER_TIMEZONE', 'UTC'),

    /*
     * -------------------------------------------------------------------------
     * User Language/Locale
     * -------------------------------------------------------------------------
     *
     * This value determines the language/locale used for displaying content
     * to users throughout the application. This is separate from the application
     * locale (APP_LOCALE) which is used for internal operations and fallback
     * translations. The user language affects how content is presented in the UI,
     * notifications, and user-facing messages.
     *
     * You can set this in your ".env" file using USER_LANGUAGE.
     * Common values: 'en', 'ja', 'es', 'fr', 'de', 'zh', etc.
     * For full locale codes: 'en_US', 'ja_JP', 'es_ES', 'fr_FR', etc.
     *
     */
    'user_language' => env('USER_LANGUAGE', 'en'),
];
