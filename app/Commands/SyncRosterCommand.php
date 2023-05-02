<?php

namespace App\Commands;

use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
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
        $failures = 0;

        $failures += ! $this->task("Retrieving roster from APM", function () {
            return ! $this->callSilently(RetrieveIcsRosterCommand::class);
        });

        $failures += ! $this->task("Uploading roster to TOsync", function () {
            return ! $this->callSilently(UploadRosterCommand::class);
        });

        if ($failures) {
            $this->error('Sync failed.');

            Log::warning('Sync failed.');

            return self::FAILURE;
        }

        $this->info('Done!');

        Log::info('Sync successful.');

        return self::SUCCESS;
    }
}
