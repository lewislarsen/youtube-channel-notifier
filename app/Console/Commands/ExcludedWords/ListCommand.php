<?php

declare(strict_types=1);

namespace App\Console\Commands\ExcludedWords;

use App\Models\ExcludedWord;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    protected $signature = 'excluded-words:list';

    protected $description = 'List all excluded words';

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
