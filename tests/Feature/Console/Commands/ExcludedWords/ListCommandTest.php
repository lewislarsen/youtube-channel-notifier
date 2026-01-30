<?php

declare(strict_types=1);

use App\Models\ExcludedWord;

beforeEach(function (): void {
    ExcludedWord::truncate();
});

describe('excluded-words:list', function (): void {
    it('displays message when no excluded words exist', function (): void {
        $this->artisan('excluded-words:list')
            ->expectsOutput('No excluded words found.')
            ->assertExitCode(0);
    });

    it('lists all excluded words when words exist', function (): void {
        ExcludedWord::create(['word' => 'live']);
        ExcludedWord::create(['word' => 'premiere']);
        ExcludedWord::create(['word' => 'trailer']);

        $this->artisan('excluded-words:list')
            ->expectsOutput('Excluded Words:')
            ->expectsOutput('==============================')
            ->expectsOutput('• live')
            ->expectsOutput('• premiere')
            ->expectsOutput('• trailer')
            ->expectsOutput('Total: 3 word(s)')
            ->assertExitCode(0);
    });

    it('lists words in alphabetical order', function (): void {
        ExcludedWord::create(['word' => 'zebra']);
        ExcludedWord::create(['word' => 'apple']);
        ExcludedWord::create(['word' => 'banana']);

        $this->artisan('excluded-words:list')
            ->expectsOutput('• apple')
            ->expectsOutput('• banana')
            ->expectsOutput('• zebra')
            ->assertExitCode(0);
    });
});
