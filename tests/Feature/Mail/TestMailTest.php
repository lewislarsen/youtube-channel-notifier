<?php

declare(strict_types=1);

use App\Mail\TestMail;

it('builds the mail correctly', function (): void {
    $mailable = new TestMail;

    $mailable->assertHasSubject('Notification email test');
    $mailable->assertSeeInText('Notification email test');
    $mailable->assertSeeInText('If you are receiving this email, your connection to your SMTP provider is working correctly.');
});
