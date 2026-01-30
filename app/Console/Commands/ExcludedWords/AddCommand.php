<?php

declare(strict_types=1);

namespace App\Console\Commands\ExcludedWords;

use App\Models\ExcludedWord;
use Illuminate\Console\Command;

class AddCommand extends Command
{
    protected $signature = 'excluded-words:add {word : The word to add}';

    protected $description = 'Add a new excluded word';

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
