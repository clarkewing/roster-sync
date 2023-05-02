<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RetrieveIcsRosterCommand extends Command
{
    protected $signature = 'retrieve';

    protected $description = 'Retrieves ICS roster file';

    public function handle(): int
    {
        try {
            // Reset FlightProgram.ics
            File::delete('storage/laravel-console-dusk/downloads/FlightProgram.ics');

            $this->browse(function ($browser) {
                // Connect to APM.
                $browser->visit('https://planning.to.aero/SAML/SingleSignOn')
                    ->waitFor('#okta-signin-username')
                    ->type('#okta-signin-username', config('app.credentials.apm.username'))
                    ->type('#okta-signin-password', config('app.credentials.apm.password'))
                    ->press('#okta-signin-submit')
                    ->waitForText('Security Question')
                    ->type('answer', config('app.credentials.apm.answer'))
                    ->press('Verify')
                    ->waitForText('Last connection date');

                // Trigger ICS download.
                $browser->visit('https://planning.to.aero/FlightProgram/GetICS')
                    ->waitUsing(10, 1, function () {
                        return File::exists('storage/laravel-console-dusk/downloads/FlightProgram.ics');
                    }, 'Waited %d seconds for FlightProgram.ics.');

            });
        } catch (Exception $e) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
