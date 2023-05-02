<?php

namespace App\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class SyncRosterCommand extends Command
{
    protected $signature = 'sync';

    protected $description = 'Sync roster between APM and TOsync.';

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyFifteenMinutes();
    }

    public function handle(): int
    {
        $this->task("Retrieving roster from APM", function () {
            return ! $this->callSilently(RetrieveIcsRosterCommand::class);
        });

        $this->task("Uploading roster to TOsync", function () {
            return ! $this->callSilently(UploadRosterCommand::class);
        });

        $this->info('Done!');

        return self::SUCCESS;
    }
}
