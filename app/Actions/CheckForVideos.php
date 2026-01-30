<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\YouTube\ExtractVideos;
use App\Actions\YouTube\FetchRssFeed;
use App\Actions\YouTube\ProcessVideos;
use App\Models\Channel;

/**
 * Class CheckForVideosAction
 *
 * This class coordinates the process of checking YouTube channels for new videos.
 * It accepts optional dependencies and will use default implementations if none are provided.
 */
class CheckForVideos
{
    private readonly FetchRssFeed $fetchRssFeed;

    private readonly ExtractVideos $extractVideos;

    private readonly ProcessVideos $processVideos;

    /**
     * Create a new instance of CheckForVideosAction with optional dependencies.
     */
    public function __construct(
        ?FetchRssFeed $fetchRssFeed = null,
        ?ExtractVideos $extractVideos = null,
        ?ProcessVideos $processVideos = null
    ) {
        $this->fetchRssFeed = $fetchRssFeed ?? resolve(FetchRssFeed::class);
        $this->extractVideos = $extractVideos ?? resolve(ExtractVideos::class);
        $this->processVideos = $processVideos ?? resolve(ProcessVideos::class);
    }

    /**
     * Executes the action to check for new videos for a given channel.
     *
     * @param  Channel  $channel  The channel to check for new videos.
     */
    public function execute(Channel $channel): void
    {
        $rssData = $this->fetchRssFeed->execute($channel);

        if (! $rssData instanceof \SimpleXMLElement) {
            return;
        }

        $newVideos = $this->extractVideos->execute($rssData, $channel);

        if (empty($newVideos)) {
            return;
        }

        $this->processVideos->execute($newVideos, $channel);
        $channel->updateLastChecked();
    }
}
