<?php

declare(strict_types=1);

namespace App\Console\Commands\ExcludedWords;

use App\Models\ExcludedWord;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('List all excluded words')]
#[Signature('excluded-words:list')]
class ListCommand extends Command
{
    public function handle(): void
    {
        $words = ExcludedWord::orderBy('word')->get();

        if ($words->isEmpty()) {
            $this->info('No excluded words found.');

            return;
        }

        $this->info('Excluded Words:');
        $this->info(str_repeat('=', 30));

        $words->each(function (ExcludedWord $excludedWord): void {
            $this->line("• {$excludedWord->word}");
        });

        $this->info("Total: {$words->count()} word(s)");
    }
}
