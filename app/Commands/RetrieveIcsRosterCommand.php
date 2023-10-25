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
                $browser
                    ->visit('https://planning.to.aero/SAML/SingleSignOn')
                    ->waitFor('input[name="identifier"]')
                    ->type('identifier', config('app.credentials.apm.username'));

                if (! $browser->pause(100)->element('input[name="credentials.passcode"]')) {
                    $browser->click('input[type="submit"]');
                }

                $browser
                    ->waitFor('input[name="credentials.passcode"]')->pause(100)
                    ->type('credentials.passcode', config('app.credentials.apm.password'))
                    ->click('input[type="submit"]');

                if ($errorElement = $browser->pause(1000)->element('.okta-form-infobox-error')) {
                    Log::error($errorElement->getText());
                    return self::FAILURE;
                }

                $browser
                    ->waitFor('.select-factor')
                    ->press('.authenticator-button[data-se="security_question"] .select-factor');

                $browser->waitFor('input[name="credentials.answer"]')->pause(100)
                    ->type('credentials.answer', config('app.credentials.apm.answer'))
                    ->click('input[type="submit"]')
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
