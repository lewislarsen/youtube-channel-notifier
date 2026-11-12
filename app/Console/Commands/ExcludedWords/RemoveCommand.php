<?php

declare(strict_types=1);

namespace App\Console\Commands\ExcludedWords;

use App\Models\ExcludedWord;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Remove an excluded word')]
#[Signature('excluded-words:remove {word : The word to remove}')]
class RemoveCommand extends Command
{
    public function handle(): void
    {
        $word = $this->argument('word');

        $excludedWord = ExcludedWord::where('word', $word)->first();

        if (! $excludedWord) {
            $this->error("The word '{$word}' was not found.");

            return;
        }

        if (! $this->confirm("Are you sure you want to remove the word '{$excludedWord->word}'?")) {
            $this->info('Operation cancelled.');

            return;
        }

        $excludedWord->delete();

        $this->info("Successfully removed excluded word: '{$word}'");
    }
}
