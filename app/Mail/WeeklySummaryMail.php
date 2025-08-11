<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Video;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

/**
 * Class WeeklySummaryMail
 *
 * This mailable class is responsible for sending a weekly summary email
 * containing a collection of videos.
 */
class WeeklySummaryMail extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param  Collection<int, Video>  $videos  A collection of video instances to include in the summary email.
     */
    public function __construct(private readonly Collection $videos)
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
                'videos' => $this->videos,
                'locale' => $this->locale,
            ]
        );
    }
}
