<?php

declare(strict_types=1);

use App\Console\Commands\ExcludedWords\RemoveCommand;
use App\Models\ExcludedWord;

beforeEach(function (): void {
    ExcludedWord::truncate();
});

describe('RemoveCommand', function (): void {
    it('removes an existing excluded word', function (): void {
        ExcludedWord::create(['word' => 'test']);

        $this->artisan(RemoveCommand::class, ['word' => 'test'])
            ->expectsConfirmation('Are you sure you want to remove the word \'test\'?', 'yes')
            ->expectsOutput("Successfully removed excluded word: 'test'")
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'test')->exists())->toBeFalse();
    });

    it('shows error when word does not exist', function (): void {
        $this->artisan(RemoveCommand::class, ['word' => 'nonexistent'])
            ->expectsOutput("The word 'nonexistent' was not found.")
            ->assertExitCode(0);
    });

    it('cancels removal when user answers no', function (): void {
        ExcludedWord::create(['word' => 'test']);

        $this->artisan(RemoveCommand::class, ['word' => 'test'])
            ->expectsConfirmation('Are you sure you want to remove the word \'test\'?', 'no')
            ->expectsOutput('Operation cancelled.')
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'test')->exists())->toBeTrue();
    });

    it('handles case-sensitive removal', function (): void {
        ExcludedWord::create(['word' => 'Test']);

        $this->artisan(RemoveCommand::class, ['word' => 'test'])
            ->expectsOutput("The word 'test' was not found.")
            ->assertExitCode(0);

        expect(ExcludedWord::where('word', 'Test')->exists())->toBeTrue();
    });
});
