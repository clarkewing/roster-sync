<?php

namespace App\DuskDrivers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use NunoMaduro\LaravelConsoleDusk\Drivers\Chrome as ConsoleDuskChrome;

class Chrome extends ConsoleDuskChrome
{
    // Overwrite Driver creation to log ALL console message levels.
    public function getDriver()
    {
        $options = (new ChromeOptions())->addArguments(
            array_filter([
                '--disable-gpu',
                $this->runHeadless(),
            ])
        );

        $driver = RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()
                ->setCapability(
                    ChromeOptions::CAPABILITY,
                    $options
                )->setCapability('goog:loggingPrefs', ['browser'=>'ALL'])
        );

        $driver->executeCustomCommand(
            '/session/:sessionId/goog/cdp/execute',
            'POST',
            [
                'cmd' => 'Browser.setDownloadBehavior',
                'params' => ['behavior' => 'allow', 'downloadPath' => config('laravel-console-dusk.paths.downloads')],
            ],
        );

        return $driver;
    }
}
