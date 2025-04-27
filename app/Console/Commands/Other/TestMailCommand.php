<?php

declare(strict_types=1);

namespace App\Console\Commands\Other;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'other:mail-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a test email to ensure the SMTP connection is working.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $emails = Config::get('app.alert_emails');

        if (empty(array_filter((array) $emails))) {
            $this->components->error('Unable to send a test email. No alert emails are set in the config.');

            return;
        }

        Mail::to($emails)->send(new TestMail);

        $this->components->success('Sent a test email to '.implode(', ', Config::get('app.alert_emails')).'.');
    }
}
