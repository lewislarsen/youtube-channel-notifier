<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification
{
    public function execute(Video $video): void
    {
        /** @var Channel $channel */
        $channel = $video->channel()->first();

        Mail::to(Config::get('app.alert_emails'))->send(new NewVideoMail($video, $channel));
    }
}
