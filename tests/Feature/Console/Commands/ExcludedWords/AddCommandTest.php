<?php

declare(strict_types=1);

use App\Console\Commands\ExcludedWords\AddCommand;
use App\Models\ExcludedWord;

beforeEach(function (): void {
    ExcludedWord::truncate();
});

describe('AddCommand', function (): void {
    it('adds a new excluded word', function (): void {
        $this->artisan(AddCommand::class, ['word' => 'test'])
            ->expectsOutput("Successfully added excluded word: 'test'")
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'test')->exists())->toBeTrue();
    });

    it('shows error when word already exists', function (): void {
        ExcludedWord::create(['word' => 'existing']);

        $this->artisan(AddCommand::class, ['word' => 'existing'])
            ->expectsOutput("The word 'existing' already exists.")
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'existing')->count())->toBe(1);
    });

    it('handles empty word gracefully', function (): void {
        $this->artisan(AddCommand::class, ['word' => ''])
            ->assertExitCode(0); // Laravel will handle validation
    });

    it('adds words with different cases', function (): void {
        $this->artisan(AddCommand::class, ['word' => 'Test'])
            ->expectsOutput("Successfully added excluded word: 'Test'")
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'Test')->exists())->toBeTrue();
    });
});
