<?php

namespace App\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Str;

class UploadRosterCommand extends Command
{
    protected $signature = 'upload';

    protected $description = 'Uploads ICS roster file to TO.sync';

    public function handle(): int
    {
        try {
            $this->browse(function ($browser) {
                // Connect to TOsync.
                $browser->visit('https://tosync.avapps.fr')
                    ->waitForText('Connexion avec identifiants TO.sync')
                    ->type('email', config('app.credentials.tosync.username'))
                    ->type('user-password', config('app.credentials.tosync.password'))
                    ->press('Connexion');

                if ($errorElement = $browser->pause(1000)->element('.noty_type__error')) {
                    Log::error($errorElement->getText());
                    return self::FAILURE;
                }

                $browser->waitFor('#calendar-container', 15);

                // Close tutorial popover if it opens.
                if ($browser->pause(1000)->element('#driver-popover-item')) {
                    $browser->whenAvailable('#driver-popover-item', function ($popover) {
                        $popover->press('Fermer');
                    });
                }

                // Open toolbar if it isn't open yet.
                if (! $browser->element('#toolbar')) {
                    $browser->waitFor('#statusButton')
                        ->press('#statusButton')
                        ->waitFor('#toolbar');
                }

                // Upload new roster.
                $browser->waitForText('Importer un fichier ics')
                    ->attach('#filereader', 'storage/laravel-console-dusk/downloads/FlightProgram.ics')
                    ->waitUsing(60, 1, function () use ($browser) {
                        $consoleLog = $browser->getOriginalBrowser()->driver->manage()->getLog('browser');

                        return Str::contains(json_encode($consoleLog), 'syncEvents');
                    }, 'Waited %d seconds for syncEvents to complete.');

                return self::SUCCESS;
            });
        } catch (Exception) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
