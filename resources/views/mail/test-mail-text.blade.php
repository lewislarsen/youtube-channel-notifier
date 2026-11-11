<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

?>
Notification email test

If you are receiving this email, your connection to your SMTP provider is working correctly.

{{ __('email.automated_notification', ['app_name' => Config::get('app.name'), 'github_url' => 'https://github.com/lewislarsen/youtube-channel-notifier']) }}
