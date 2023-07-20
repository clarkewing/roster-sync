<?php

namespace App\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
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
                    ->waitFor('input[name="username"]')
                    ->type('username', config('app.credentials.apm.username'))
                    ->press('#okta-signin-submit')
                    ->waitFor('input[name="password"]')->pause(100)
                    ->type('password', config('app.credentials.apm.password'))
                    ->press('Verify');

                if ($errorElement = $browser->pause(1000)->element('.okta-form-infobox-error')) {
                    Log::error($errorElement->getText());
                    return self::FAILURE;
                }

                $browser->waitFor('input[name="answer"]')->pause(100)
                    ->type('answer', config('app.credentials.apm.answer'))
                    ->press('Verify')
                    ->waitForText('Last connection date', 15);

                // Trigger ICS download.
                $browser->visit('https://planning.to.aero/FlightProgram/GetICS')
                    ->waitUsing(30, 1, function () {
                        return File::exists('storage/laravel-console-dusk/downloads/FlightProgram.ics');
                    }, 'Waited %d seconds for FlightProgram.ics.');

            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
