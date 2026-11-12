<?php

declare(strict_types=1);

namespace App\Console\Commands\ExcludedWords;

use App\Models\ExcludedWord;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Add a new excluded word')]
#[Signature('excluded-words:add {word : The word to add}')]
class AddCommand extends Command
{
    public function handle(): void
    {
        $word = $this->argument('word');

        if (ExcludedWord::where('word', $word)->exists()) {
            $this->error("The word '{$word}' already exists.");

            return;
        }

        ExcludedWord::create(['word' => $word]);

        $this->info("Successfully added excluded word: '{$word}'");
    }
}
