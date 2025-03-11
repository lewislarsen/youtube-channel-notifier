<?php

declare(strict_types=1);

namespace App\Actions\YouTube;

use Symfony\Component\DomCrawler\Crawler;

/**
 * A version of ExtractYouTubeChannelId that supports custom debug callbacks.
 * This class extends the base extractor and adds the ability to receive
 * debug information through callbacks instead of just logging.
 */
class DebuggableExtractYouTubeChannelId extends ExtractYouTubeChannelId
{
    /**
     * Debug callback function.
     *
     * @var callable|null
     */
    private $debugCallback = null;

    /**
     * Set the debug callback function.
     */
    public function setDebugCallback(callable $callback): self
    {
        $this->debugCallback = $callback;

        return $this;
    }

    /**
     * Override the parent debug method to also send messages to the callback.
     */
    protected function debug(string $message): void
    {
        // First call the parent method to ensure logging still happens
        parent::debug($message);

        // Then send to the callback if it exists
        if ($this->debugCallback) {
            call_user_func($this->debugCallback, $message);
        }
    }

    /**
     * Override the parent warn method to also send warnings to the callback.
     */
    protected function warn(string $message): void
    {
        parent::warn($message);

        if ($this->debugCallback) {
            call_user_func($this->debugCallback, $message, 'warn');
        }
    }

    /**
     * Save HTML to a file for debugging purposes with additional custom information.
     */
    protected function saveHtmlForDebug(string $html, Crawler $crawler): void
    {
        parent::saveHtmlForDebug($html, $crawler);

        if ($this->debugCallback) {
            $this->analyzePageStructure($crawler);
        }
    }

    /**
     * Analyze the page structure for YouTube-specific elements.
     */
    private function analyzePageStructure(Crawler $crawler): void
    {
        $this->debug('Analyzing YouTube page structure for debugging:');

        $this->checkElementExistence($crawler, '//body[@id="body"]', 'Main body tag with ID "body"');
        $this->checkElementExistence($crawler, '//div[@id="content"]', 'Content container');
        $this->checkElementExistence($crawler, '//div[@id="page-manager"]', 'Page manager container');
        $this->checkElementExistence($crawler, '//ytd-app', 'YouTube app component');
        $this->checkElementExistence($crawler, '//ytd-watch-flexy', 'Watch page component');
        $this->checkElementExistence($crawler, '//ytd-channel-page', 'Channel page component');
        $this->checkElementExistence($crawler, '//*[contains(text(), "This video isn\'t available")]', 'Video unavailable message');
        $this->checkElementExistence($crawler, '//*[contains(text(), "age-restricted")]', 'Age restriction notice');
        $this->checkElementExistence($crawler, '//*[contains(text(), "content warning")]', 'Content warning');
        $this->checkElementExistence($crawler, '//*[contains(text(), "Sign in to confirm your age")]', 'Age verification gate');
        $this->checkElementExistence($crawler, '//*[contains(@action, "consent")]', 'Consent form');
        $this->checkElementExistence($crawler, '//*[contains(text(), "I agree")]', 'Consent button');
    }

    /**
     * Check if an element exists in the page and report its presence/absence.
     */
    private function checkElementExistence(Crawler $crawler, string $xpath, string $description): void
    {
        $elements = $crawler->filterXPath($xpath);
        $count = $elements->count();

        if ($count > 0) {
            $this->debug("✓ Found {$count} elements matching: {$description}");
        } else {
            $this->debug("✗ Did not find: {$description}");
        }
    }
}
