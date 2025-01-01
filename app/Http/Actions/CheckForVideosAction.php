<?php

namespace App\Http\Actions;

use App\Mail\NewVideoMail;
use App\Models\Channel;
use App\Models\Video;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckForVideosAction
{
    public function execute(Channel $channel): void
    {
        $channelId = $channel->getAttributeValue('channel_id');
        $channelName = $channel->getAttributeValue('name');

        $rssUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId;

        // Fetch RSS feed
        $response = Http::get($rssUrl);

        if ($response->failed()) {
            Log::error("Failed to fetch RSS feed for channel: $channelName. Response: ".$response->body());

            return;
        }

        // Parse RSS feed
        $rssData = simplexml_load_string($response->body());
        if (! $rssData || ! isset($rssData->entry)) {
            Log::info("No videos found in RSS feed for channel: $channelName.");

            return;
        }

        // Extract existing video IDs from the database
        $existingVideoIds = Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
        $newVideos = [];

        foreach ($rssData->entry as $entry) {
            $rawVideoId = (string) $entry->id; // e.g., "yt:video:5ltAy1W6k-Q"
            $videoId = str_replace('yt:video:', '', $rawVideoId); // Remove "yt:video:" prefix
            $title = (string) $entry->title;
            $publishedAt = Carbon::parse((string) $entry->published);

            // Skip if video already exists in the database
            if (in_array($videoId, $existingVideoIds, true)) {
                continue;
            }

            $newVideos[] = [
                'video_id' => $videoId,
                'title' => $title,
                'description' => (string) $entry->summary,
                'published_at' => $publishedAt,
                'channel_id' => $channel->id,
            ];
        }

        // Handle first-time import of video data (no emails)
        if (! $channel->last_checked_at) {
            Video::insert($newVideos); // Bulk insert
            Log::info("First-time import for channel: $channelName completed with ".count($newVideos).' videos.');
        } else {
            // Insert new videos and send email notifications
            foreach ($newVideos as $videoData) {
                $video = Video::create($videoData);
                Mail::to('lewis@larsens.dev')->send(new NewVideoMail($video));
                Log::info("New video added: {$video->title} ({$video->video_id}) for channel: $channelName.");
            }
        }

        // Update the channel's last_checked_at timestamp
        $channel->update(['last_checked_at' => now()]);

        Log::info("Check for videos completed for channel: $channelName.");
    }
}
