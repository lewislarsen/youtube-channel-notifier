<?php

declare(strict_types=1);

use App\Console\Commands\Other\TestMailCommand;
use App\Mail\TestMail;

it('sends a test email to all alert emails', function (): void {
    Mail::fake();
    Config::set('app.alert_emails', ['alert1@email.com', 'alert2@email.com']);

    $this->artisan(TestMailCommand::class)
        ->expectsOutputToContain('Sent a test email to alert1@email.com, alert2@email.com.');

    Mail::assertSent(TestMail::class, function ($mail) {
        return $mail->hasTo('alert1@email.com') &&
               $mail->hasTo('alert2@email.com');
    });

});

it('does not send a test email if there are no emails set', function (): void {
    Mail::fake();
    Config::set('app.alert_emails', []);

    $this->artisan(TestMailCommand::class)
        ->expectsOutputToContain('Unable to send a test email. No alert emails are set in the config.');

    Mail::assertNotSent(TestMail::class);
});
