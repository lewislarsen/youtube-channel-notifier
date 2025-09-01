<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Class WeeklySummaryMail
 *
 * This mailable class is responsible for sending a weekly summary email
 * containing videos grouped by weekdays.
 */
class WeeklySummaryMail extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param  array<string, array{date: \Carbon\Carbon, videos: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Video>}>  $weekdays  An array of weekdays with their videos, grouped by date.
     */
    public function __construct(private readonly array $weekdays)
    {
        $this->locale = config('app.user_language', 'en');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    /**
     * Generate the subject line for the envelope.
     */
    protected function getSubject(): string
    {
        return __('email.weekly_summary_subject');
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.weekly-summary-mail',
            with: [
                'weekdays' => $this->weekdays,
                'locale' => $this->locale,
            ]
        );
    }
}
