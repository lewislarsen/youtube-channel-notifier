<?php

namespace App\Mail;

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class NewVideoMail
 *
 * This mailable class is responsible for creating an email notification
 * for new videos uploaded to a YouTube channel. It includes the video's
 * title, URL, and publication date, along with the channel name.
 */
class NewVideoMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  Video  $video  The video instance containing video details.
     * @param  Channel  $channel  The channel instance containing channel details.
     */
    public function __construct(
        public Video $video,
        public Channel $channel = new Channel
    ) {
        $this->channel = $video->channel;
    }

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
        return sprintf('%s - New Video: %s', $this->channel->name, $this->video->title);
    }

    /**
     * Get the message content definition.
     *
     * @return Content The content instance with markdown and data.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-video-mail',
            with: [
                'videoCreator' => $this->channel->name,
                'videoTitle' => $this->video->title,
                'videoUrl' => $this->video->getYoutubeUrl(),
                'published' => $this->video->getFormattedPublishedDate(),
                'thumbnailUrl' => $this->video->getThumbnailUrl('maxresdefault'),
            ],
        );
    }
}
