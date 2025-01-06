<?php

namespace App\Mail;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewVideoMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Video $video,
        public Channel $channel = new Channel
    ) {
        $this->channel = $video->channel;
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
        return sprintf('%s - New Video: %s', $this->channel->name, $this->video->title);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-video-mail',
            with: [
                'videoCreator' => $this->channel->name,
                'videoTitle' => $this->video->title,
                'videoUrl' => 'https://www.youtube.com/watch?v='.$this->video->video_id,
                'published' => $this->video->published_at->format('d M Y h:i A'),
            ],
        );
    }
}
