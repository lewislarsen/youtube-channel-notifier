<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class TestMail
 *
 * This mailable class is responsible for sending a test email
 * to ensure the operational status of the mail endpoint.
 */
class TestMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct() {}

    /**
     * Get the message envelope.
     *
     * @return Envelope The envelope instance with the subject line.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    /**
     * Generate the subject line for the envelope.
     *
     * @return string The generated subject line.
     */
    protected function getSubject(): string
    {
        return 'Notification email test';
    }

    /**
     * Get the message content definition.
     *
     * @return Content The content instance with markdown and data.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.test-mail',
        );
    }
}
