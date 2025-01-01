<?php

namespace App\Mail;

use App\Models\Video;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewVideoMail extends Mailable
{
    use SerializesModels;

    /**
     * The video instance.
     */
    public Video $video;

    /**
     * Create a new message instance.
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Video Uploaded: '.$this->video->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-video-mail',
            with: [
                'videoTitle' => $this->video->title,
                'videoUrl' => 'https://www.youtube.com/watch?v='.$this->video->video_id,
                'publishedAt' => $this->video->published_at->toFormattedDateString(),
            ],
        );
    }
}
