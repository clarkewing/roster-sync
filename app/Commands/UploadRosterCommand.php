<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Command;
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
                    ->press('Connexion')
                    ->waitFor('#calendar-container', 10);

                if ($browser->pause(1000)->element('#driver-popover-item')) {
                    $browser->whenAvailable('#driver-popover-item', function ($popover) {
                        $popover->press('Fermer');
                    });
                }

                // Upload new roster.
                $browser->waitFor('#statusButton')
                    ->press('#statusButton')
                    ->waitForText('Importer un fichier ics')
                    ->attach('#filereader', 'storage/laravel-console-dusk/downloads/FlightProgram.ics')
                    ->waitUsing(60, 1, function () use ($browser) {
                        $consoleLog = $browser->getOriginalBrowser()->driver->manage()->getLog('browser');

                        return Str::contains(json_encode($consoleLog), 'syncEvents');
                    }, 'Waited %d seconds for syncEvents to complete.');

                return self::SUCCESS;
            });
        } catch (Exception $e) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
