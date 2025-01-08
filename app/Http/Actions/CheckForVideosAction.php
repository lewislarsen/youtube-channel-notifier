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
        $rssData = $this->fetchRssFeed($channel);
        if (is_null($rssData)) {
            return;
        }

        $newVideos = $this->extractNewVideos($rssData, $channel);
        if (empty($newVideos)) {
            return;
        }

        is_null($channel->last_checked_at)
            ? $this->firstTimeImport($newVideos, $channel)
            : $this->insertNewVideosAndNotify($newVideos, $channel);

        $this->updateChannelLastChecked($channel);
    }

    private function fetchRssFeed(Channel $channel): ?object
    {
        $rssUrl = sprintf('https://www.youtube.com/feeds/videos.xml?channel_id=%s', $channel->channel_id);
        $response = Http::get($rssUrl);

        if ($response->failed()) {
            Log::error("Failed to fetch RSS feed for channel: {$channel->name}. Response: {$response->body()}");

            return null;
        }

        $rssData = simplexml_load_string($response->body());

        if (! $rssData || ! isset($rssData->entry)) {
            Log::info("No videos found in RSS feed for channel: {$channel->name}.");

            return null;
        }

        return $rssData;
    }

    private function extractNewVideos(object $rssData, Channel $channel): array
    {
        $existingVideoIds = Video::where('channel_id', $channel->id)->pluck('video_id')->toArray();
        $newVideos = [];

        foreach ($rssData->entry as $entry) {
            $videoId = str_replace('yt:video:', '', (string) $entry->id);
            $title = (string) $entry->title;

            // Check if the title contains the exact word "LIVE"
            if (stripos($title, 'LIVE') !== false && preg_match('/\bLIVE\b/', $title)) {
                Log::warning("Video with title containing 'LIVE' found and ignored: {$title}");

                continue;
            }

            if (in_array($videoId, $existingVideoIds, true)) {
                continue;
            }

            $newVideos[] = [
                'video_id' => $videoId,
                'title' => $title,
                'description' => (string) $entry->summary,
                'published_at' => Carbon::parse((string) $entry->published),
                'channel_id' => $channel->id,
            ];
        }

        return $newVideos;
    }

    private function firstTimeImport(array $newVideos, Channel $channel): void
    {
        Video::insert($newVideos);
        Log::info("First-time import for channel: {$channel->name} completed with ".count($newVideos).' videos.');
    }

    private function insertNewVideosAndNotify(array $newVideos, Channel $channel): void
    {
        foreach ($newVideos as $videoData) {
            $video = Video::create($videoData);
            Mail::to('lewis@larsens.dev')->send(new NewVideoMail($video));
            Log::info("New video added: {$video->title} ({$video->video_id}) for channel: {$channel->name}.");
        }
    }

    private function updateChannelLastChecked(Channel $channel): void
    {
        $channel->update(['last_checked_at' => now()]);
        Log::info("Check for videos completed for channel: {$channel->name}.");
    }
}
